<?php

namespace App\Base\Controller\Admin;

use App\Base\Service\FaqService;
use App\Base\Model\Admin\FaqCategoryRequest;
use App\Base\Model\Admin\FaqCategorySearchRequest;
use App\Base\Model\Admin\FaqRequest;
use Core\BaseController;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class FaqController extends BaseController
{
    private FaqService $service;

    public function __construct(
        FaqService $service,
    ) {
        $this->service = $service;
    }

    /**
     * FAQ 카테고리 목록 페이지
     */
    public function index(Request $request, Response $response, FaqCategorySearchRequest $search_request): Response
    {
        // 검색 조건 설정
        $params = $search_request->publics();

        // 총 데이터 수 조회 및 페이징 정보 설정
        $total_count = $this->service->fetchFaqCategoriesCount($params);
        $search_request->setTotalCount($total_count);

        // FAQ 카테고리 목록 조회
        $faq_categories = $this->service->getFaqCategories($params);

        $response_data = [
            "faq_categories" => $faq_categories,
            "total_count" => $total_count,
            "search" => $search_request,
            "pagination" => $search_request->getPaginationInfo(),
            "query_params" => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/faq/category_list.html', $response_data);
    }

    /**
     * FAQ 카테고리 등록
     */
    public function insertCategory(Request $request, Response $response, FaqCategoryRequest $data): Response
    {
        $this->service->insertCategory($data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.faq');
    }

    /**
     * FAQ 카테고리 수정
     */
    public function updateCategory(Request $request, Response $response, FaqCategoryRequest $data, string $faq_category_id): Response
    {
        $faq_category = $this->service->getFaqCategory($faq_category_id);

        $this->service->updateCategory($faq_category['id'], $data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.faq');
    }

    /**
     * FAQ 카테고리 삭제
     */
    public function deleteCategory(Response $response, string $faq_category_id): Response
    {
        $faq_category = $this->service->getFaqCategory($faq_category_id);

        $this->service->deleteFaqs($faq_category['id']);
        $this->service->deleteCategory($faq_category['id']);

        return $response->withJson(['message' => 'FAQ 카테고리가 삭제되었습니다.']);
    }

    /**
     * FAQ 항목 목록 페이지
     */
    public function list(Request $request, Response $response, string $faq_category_id): Response
    {
        $faq_category = $this->service->getFaqCategory($faq_category_id);
        $faqs = $this->service->getFaqs($faq_category_id);

        $response_data = [
            "faq_category" => $faq_category,
            "faqs" => $faqs,
            "total_count" => count($faqs),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/faq/list.html', $response_data);
    }

    /**
     * FAQ 항목 등록 폼 페이지
     */
    public function create(Request $request, Response $response, string $faq_category_id): Response
    {
        $faq_category = $this->service->getFaqCategory($faq_category_id);

        $response_data = [
            "faq_category" => $faq_category,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/faq/form.html', $response_data);
    }

    /**
     * FAQ 항목 등록
     */
    public function insert(Request $request, Response $response, FaqRequest $data, string $faq_category_id): Response
    {
        $this->service->insert($faq_category_id, $data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.faq.list', ['faq_category_id' => $faq_category_id]);
    }

    /**
     * FAQ 항목 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $faq_id): Response
    {
        $faq = $this->service->getFaq($faq_id);
        $faq_category = $this->service->getFaqCategory($faq['faq_category_id']);

        $response_data = [
            "faq_category" => $faq_category,
            "faq" => $faq,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '@admin/content/faq/form.html', $response_data);
    }

    /**
     * FAQ 항목 수정
     */
    public function update(Request $request, Response $response, FaqRequest $data, string $faq_id): Response
    {
        $faq = $this->service->getFaq($faq_id);

        $this->service->update($faq['id'], $data->publics());

        return $this->redirectRoute($request, $response, 'admin.content.faq.list', ['faq_category_id' => $faq['faq_category_id']]);
    }

    /**
     * FAQ 항목 삭제
     */
    public function delete(Response $response, string $faq_id): Response
    {
        $faq = $this->service->getFaq($faq_id);

        $this->service->delete($faq['id']);

        return $response->withJson(['message' => 'FAQ 항목이 삭제되었습니다.']);
    }
}
