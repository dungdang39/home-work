<?php

namespace App\Base\Controller\Admin;


use App\Base\Model\Admin\BoardSearchRequest;
use App\Base\Model\Admin\CreateBoardRequest;
use App\Base\Model\Admin\UpdateBoardRequest;
use App\Base\Service\BoardService;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class BoardController extends BaseController
{
    private BoardService $service;

    public function __construct(
        BoardService $service,
    ) {
        $this->service = $service;
    }

    /**
     * 게시판 목록 페이지
     */
    public function index(Request $request, Response $response, BoardSearchRequest $search): Response
    {
        // 검색 조건 설정
        $search_params = $search->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchBoardsTotalCount($search_params);
        $search->setTotalCount($total_count);

        // 게시판 목록 조회
        $boards = $this->service->getBoards($search_params);

        $response_data = [
            "boards" => $boards,
            "total_count" => $total_count,
            "search" => $search,
            "pagination" => $search->getPaginationInfo(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/community/board/list.html', $response_data);
    }

    /**
     * 게시판 등록 폼 페이지
     */
    public function create(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/community/board/form.html');
    }

    /**
     * 게시판 등록
     */
    public function insert(Request $request, Response $response, CreateBoardRequest $data): Response
    {
        $this->service->insert($data->publics());

        return $this->redirectRoute($request, $response, 'admin.community.board');
    }

    /**
     * 게시판 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $board_id): Response
    {
        $board = $this->service->getBoard($board_id);

        $response_data = [
            'board' => $board,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/community/board/form.html', $response_data);
    }

    /**
     * 게시판 수정
     */
    public function update(Request $request, Response $response, UpdateBoardRequest $data, string $board_id): Response
    {
        $board = $this->service->getBoard($board_id);

        $this->service->update($board['board_id'], $data->publics());

        return $this->redirectRoute($request, $response,
            'admin.community.board.view',
            ['board_id' => $board['board_id']],
            $request->getQueryParams()
        );
    }

    /**
     * 게시판 삭제
     * @todo 게시판 삭제시 함께 삭제해야할 내용 추가
     */
    public function delete(Request $request, Response $response, string $board_id): Response
    {
        $board = $this->service->getBoard($board_id);
        
        $this->service->delete($board['board_id']);

        return $response->withJson(['message' => '게시판이 삭제되었습니다.']);
    }

    /**
     * 게시판 전시순서 변경
     */
    public function updateListOrder(Request $request, Response $response): Response
    {
        return $this->redirectRoute($request, $response, 'admin.community.board');
    }
}