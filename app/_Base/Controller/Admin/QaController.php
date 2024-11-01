<?php

namespace App\Base\Controller\Admin;

use App\Base\Model\Admin\QaSearchRequest;
use App\Base\Model\Admin\UpdateQaReqeust;
use App\Base\Service\QaConfigService;
use App\Base\Service\QaService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class QaController extends BaseController
{
    private QaService $service;
    private QaConfigService $config_service;

    public function __construct(
        QaService $service,
        QaConfigService $config_service
    ) {
        $this->service = $service;
        $this->config_service = $config_service;
    }

    /**
     * Q&A 목록 페이지
     */
    public function index(Request $request, Response $response, QaSearchRequest $search_request): Response
    {
        // 검색 조건 설정
        $search_params = $search_request->publics();

        // 검색 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchQuestionsTotalCount($search_params);
        $search_request->setTotalCount($total_count);

        // Q&A 목록 조회
        $questions = $this->service->getQuestions($search_params);
        // 카테고리 목록 조회
        $categories = $this->config_service->getCategories();

        $response_data = [
            'categories' => $categories,
            'questions' => $questions,
            'total_count' => $total_count,
            'search' => $search_request,
            'pagination' => $search_request->getPaginationInfo(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/qa/list.html', $response_data);
    }

    /**
     * Q&A 상세 폼 페이지
     */
    public function view(Request $request, Response $response, int $id): Response
    {
        $question = $this->service->getQuestion($id);
        $answer = $this->service->getAnswerByQuestion($question['id']);

        $response_data = [
            'question' => $question,
            'answer' => $answer,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/member/qa/form.html', $response_data);
    }

    /**
     * Q&A 수정
     * @todo 답변 등록시 알림 발송
     */
    public function updateQa(Request $request, Response $response, int $id, UpdateQaReqeust $data): Response
    {
        $question = $this->service->getQuestion($id);
        $answer = $this->service->getAnswerByQuestion($question['id']);

        $this->service->updateQuestion($question['id'], ['status' => $data->status]);
        if (empty($answer) && $data->content) {
            $login_member = $request->getAttribute('login_member');
            $this->service->createAnswer([
                'question_id' => $question['id'],
                'admin_id' => $login_member['mb_id'],
                'content' => $data->content
            ]);
        } elseif ($answer) {
            $this->service->updateAnswer($answer['id'], ['content' => $data->content]);
        }
        
        return $this->redirectRoute($request, $response, 'admin.member.qa.manage.view', ['id' => $id]);
    }

    /**
     * Q&A 삭제
     */
    public function delete(Request $request, Response $response, int $id): Response
    {
        $question = $this->service->getQuestion($id);

        $this->service->deleteQa($question['id']);

        return $response->withJson(['message' => '삭제되었습니다.']);
    }
}
