<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\MemberService;
use App\Base\Model\Admin\CreateMemberRequest;
use App\Base\Model\Admin\DeleteMemberListRequest;
use App\Base\Model\Admin\MemberMemoRequest;
use App\Base\Model\Admin\MemberNotificationRequest;
use App\Base\Model\Admin\MemberSearchRequest;
use App\Base\Model\Admin\MemberRequest;
use App\Base\Model\Admin\UpdateMemberListRequest;
use App\Base\Service\ConfigService;
use App\Base\Service\SocialProfileService;
use Core\BaseController;
use Core\FileService;
use Core\ImageService;
use Core\Lib\FlashMessage;
use Core\Validator\Validator;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class MemberController extends BaseController
{
    private ConfigService $config_service;
    private FileService $file_service;
    private FlashMessage $flash;
    private ImageService $image_service;
    private MemberService $service;
    private SocialProfileService $social_service;

    public function __construct(
        Container $container,
        ConfigService $config_service,
        FileService $file_service,
        ImageService $image_service,
        MemberService $service,
        SocialProfileService $social_service
    ) {
        $this->config_service = $config_service;
        $this->file_service = $file_service;
        $this->flash = $container->get('flash');
        $this->image_service = $image_service;
        $this->service = $service;
        $this->social_service = $social_service;
    }

    /**
     * 회원 목록 페이지
     */
    public function index(Request $request, Response $response, MemberSearchRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchMembersTotalCount($search_params);
        $search_request->setTotalCount($total_count);

        // 회원 목록 조회
        $members = $this->service->getMembers($search_params);

        $response_data = [
            "members" => $members,
            "total_count" => $total_count,
            "search" => $search_request,
            "pagination" => $search_request->getPaginationInfo(),
            "query_params" => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/list.html', $response_data);
    }

    /**
     * 회원 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/form.html');
    }

    /**
     * 회원 등록
     */
    public function insert(Request $request, Response $response, CreateMemberRequest $data): Response
    {
        $data->mb_image = $this->image_service->upload(
            $request,
            MemberService::DIRECTORY,
            $data->mb_image_file,
            MemberService::IMAGE_WIDTH,
            MemberService::IMAGE_HEIGHT
        ) ?: null;

        unset($data->mb_image_file);

        $this->service->createMember($data->publics());

        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }

    /**
     * 회원 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $mb_id): Response
    {
        $member = $this->service->getMember($mb_id);

        $socials = $this->social_service->getMemberSocialProfiles($mb_id);

        $response_data = [
            "member" => $member,
            "socials" => $socials,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/form.html', $response_data);
    }

    /**
     * 회원 수정
     */
    public function update(Request $request, Response $response, MemberRequest $data, string $mb_id): Response
    {
        $login_member = $request->getAttribute('login_member');
        $login_id = $login_member['mb_id'];
        $member = $this->service->getMember($mb_id);

        $is_super_admin_login_member = $this->config_service->isSuperAdmin($login_id);
        $is_super_admin_member = $this->config_service->isSuperAdmin($mb_id);

        if (!$is_super_admin_login_member && $member['mb_level'] >= $login_member['mb_level']) {
            throw new Exception('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.', 403);
        }
        if (
            !$is_super_admin_login_member
            && $is_super_admin_member
        ) {
            throw new Exception('최고관리자의 비밀번호를 수정할수 없습니다.', 403);
        }
        if (
            $login_id === $mb_id
            && $member['mb_level'] != $data->mb_level
        ) {
            throw new Exception('로그인 중인 관리자 레벨은 수정할 수 없습니다.', 403);
        }
        if ($data->mb_leave_date || $data->mb_intercept_date) {
            if (
                $login_id === $mb_id
                || $is_super_admin_member
            ) {
                throw new Exception('해당 관리자의 탈퇴 일자 또는 접근 차단 일자를 수정할 수 없습니다.', 403);
            }
        }

        if ($data->mb_image_del || Validator::isUploadedFile($data->mb_image_file)) {
            $this->file_service->deleteByDb($request, $member['mb_image']);
            $data->mb_image = $this->image_service->upload(
                $request,
                MemberService::DIRECTORY,
                $data->mb_image_file,
                MemberService::IMAGE_WIDTH,
                MemberService::IMAGE_HEIGHT
            );
        }
        unset($data->mb_image_del);
        unset($data->mb_image_file);

        $this->service->updateMember($member, $data->publics());

        return $this->redirectRoute($request, $response, 'admin.member.manage.view', ['mb_id' => $member['mb_id']]);
    }

    /**
     * 회원 삭제
     * - 실제 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     */
    public function delete(Request $request, Response $response, string $mb_id): Response
    {
        $login_member = $request->getAttribute('login_member');
        $member = $this->service->getMember($mb_id);

        if ($member['mb_id'] === $login_member['mb_id']) {
            throw new Exception('로그인 중인 관리자는 삭제할 수 없습니다.', 403);
        }
        if ($this->config_service->isSuperAdmin($mb_id)) {
            throw new Exception('최고관리자는 삭제할 수 없습니다.', 403);
        }
        if ($member['mb_level'] >= $login_member['mb_level']) {
            throw new Exception('자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.', 403);
        }

        $this->service->leaveMember($member);

        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }

    /**
     * 회원정보 조회
     */
    public function searchMembers(Request $request, Response $response): Response
    {
        $params['field'] = $request->getQueryParams()['search_type'];
        $params['keyword'] = $request->getQueryParams()['keyword'];
        $members = $this->service->getMembers($params);

        return $response->withJson([
            'message' => "회원정보 조회가 완료되었습니다.",
            'members' => $members
        ], 200);
    }

    /**
     * 회원정보 조회
     */
    public function getMemberInfo(Response $response, string $mb_id): Response
    {
        $member = $this->service->getMember($mb_id);

        return $response->withJson([
            'message' => "회원정보 조회가 완료되었습니다.",
            'member' => $member
        ], 200);
    }

    /**
     * 회원 메모 수정
     */
    public function updateMemo(Request $request, Response $response, MemberMemoRequest $data, string $mb_id): Response
    {
        $member = $this->service->getMember($mb_id);

        $this->service->update($member['mb_id'], $data->publics());

        return $response->withJson([
            'message' => '메모가 ' . ($data->mb_memo === '') ? '삭제' : '수정' . '되었습니다.',
        ], 200);
    }

    /**
     * 회원 알림 전송
     * @todo 알림 전송 기능 추가
     */
    public function sendNotification(Request $request, Response $response, MemberNotificationRequest $data, string $mb_id): Response
    {
        $member = $this->service->getMember($mb_id);

        // 알림 전송처리

        return $response->withJson(['message' => $member['mb_name'] . '님께 알림이 전송되었습니다.'], 200);
    }

    /**
     * 회원 정보 일괄 수정
     */
    public function updateList(Request $request, Response $response, UpdateMemberListRequest $data): Response
    {
        $login_member = $request->getAttribute('login_member');
        $is_super_admin_login_member = $this->config_service->isSuperAdmin($login_member['mb_id']);

        $errors = [];

        foreach ($data->members as $mb_id => $list_data) {
            $member_info = $this->service->fetchMemberById($mb_id);
            if (!$member_info) {
                $errors[] = "{$mb_id} : 회원정보가 존재하지 않습니다.";
                continue;
            }
            if ($member_info['mb_id'] === $login_member['mb_id']) {
                $errors[] = "{$mb_id} : 로그인 중인 관리자는 수정할 수 없습니다.";
                continue;
            }
            if (!$is_super_admin_login_member && $member_info['mb_level'] >= $login_member['mb_level']) {
                $errors[] = "{$mb_id} : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.";
                continue;
            }

            if ($member_info['mb_leave_date'] && $list_data['mb_leave_date']) {
                unset($list_data['mb_leave_date']);
            }
            if ($member_info['mb_intercept_date'] && $list_data['mb_intercept_date']) {
                unset($list_data['mb_intercept_date']);
            }

            $this->service->update($mb_id, $list_data);
        }

        $this->flash->setMessage($errors);
        return $this->redirectRoute($request, $response, 'admin.member.manage', [], $request->getQueryParams());
    }

    /**
     * 회원 정보 일괄 삭제
     */
    public function deleteList(Request $request, Response $response, DeleteMemberListRequest $data): Response
    {
        $login_member = $request->getAttribute('login_member');

        $errors = [];

        foreach ($data->members as $mb_id) {
            $member_info = $this->service->getMember($mb_id);

            if (!$member_info) {
                $errors[] = "{$mb_id} : 회원정보가 존재하지 않습니다.";
                continue;
            }
            if ($member_info['mb_id'] === $login_member['mb_id']) {
                $errors[] = "{$mb_id} : 로그인 중인 관리자는 삭제할 수 없습니다.";
                continue;
            }
            if ($this->config_service->isSuperAdmin($member_info['mb_id'])) {
                $errors[] = "{$mb_id} : 최고 관리자는 삭제할 수 없습니다.";
                continue;
            }
            if (!$this->config_service->isSuperAdmin($login_member['mb_id']) && $member_info['mb_level'] >= $login_member['mb_level']) {
                $errors[] = "{$mb_id} : 자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.";
                continue;
            }

            $this->service->deleteMember($member_info);
        }

        $this->flash->setMessage($errors);
        return $this->redirectRoute($request, $response, 'admin.member.manage');
    }
}