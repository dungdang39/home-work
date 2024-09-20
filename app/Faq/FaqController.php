<?php

namespace App\Faq;

use App\Faq\FaqService;
use App\Faq\Model\FaqCategoryRequest;
use App\Faq\Model\FaqCategorySearchRequest;
use App\Faq\Model\FaqRequest;
use Core\BaseController;
use Core\Model\PaginationRequest;
use DI\Container;
use Exception;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Views\Twig;

class FaqController extends BaseController
{
    private FaqService $service;

    public function __construct(
        Container $container,
        FaqService $service,
    ) {
        parent::__construct($container);

        $this->service = $service;
    }

    /**
     * FAQ 카테고리 목록 페이지
     */
    public function index(Request $request, Response $response): Response
    {
        // 검색 조건 설정
        $search_params = FaqCategorySearchRequest::createFromQueryParams($request)->toArray();

        // 페이지 설정
        $total_count = $this->service->fetchFaqCategoriesTotalCount($search_params);
        $page_params = PaginationRequest::createFromQueryParams($request)->toArray();
        $page_params['total_count'] = $total_count;
        $page_params['total_page'] = ceil($total_count / $page_params['limit']);
        $params = array_merge($search_params, $page_params);


        $faq_categories = $this->service->getFaqCategories($params);

        $response_data = [
            "faq_categories" => $faq_categories,
            "total_count" => $total_count,
            "search" => $search_params,
            "pagination" => $page_params,
            "query_params" => $request->getQueryParams(),
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/faq_category_list.html', $response_data);
    }

    /**
     * FAQ 카테고리 등록
     */
    public function insertCategory(Request $request, Response $response): Response
    {
        try {
            $request_body = $request->getParsedBody();
            $data = new FaqCategoryRequest($request_body);

            $this->service->insertCategory($data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.faq');
    }

    /**
     * FAQ 카테고리 수정
     */
    public function updateCategory(Request $request, Response $response, string $faq_category_id): Response
    {
        try {
            $faq_category = $this->service->getFaqCategory($faq_category_id);
            $request_body = $request->getParsedBody();
            $data = new FaqCategoryRequest($request_body);

            $this->service->updateCategory($faq_category['id'], $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.faq');
    }

    /**
     * FAQ 카테고리 삭제
     */
    public function deleteCategory(Request $request, Response $response, string $faq_category_id): Response
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
        return $view->render($response, '/admin/faq_list.html', $response_data);
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
        return $view->render($response, '/admin/faq_form.html', $response_data);
    }

    /**
     * FAQ 항목 등록
     */
    public function insert(Request $request, Response $response, string $faq_category_id): Response
    {
        try {
            $data = new FaqRequest($request->getParsedBody());

            $this->service->insert($faq_category_id, $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.faq.list', ['faq_category_id' => $faq_category_id]);
    }

    /**
     * FAQ 항목 상세 폼 페이지
     */
    public function view(Request $request, Response $response, string $faq_category_id, string $faq_id): Response
    {
        $faq = $this->service->getFaq($faq_id);
        $faq_category = $this->service->getFaqCategory($faq['faq_category_id']);

        $response_data = [
            "faq_category" => $faq_category,
            "faq" => $faq,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/faq_form.html', $response_data);
    }

    /**
     * FAQ 항목 수정
     */
    public function update(Request $request, Response $response, string $faq_category_id, string $faq_id): Response
    {
        try {
            $faq = $this->service->getFaq($faq_id);
            $data = new FaqRequest($request->getParsedBody());

            $this->service->update($faq['id'], $data->toArray());
        } catch (Exception $e) {
            return $this->handleException($request, $response, $e);
        }

        return $this->redirectRoute($request, $response, 'admin.content.faq.list', ['faq_category_id' => $faq['faq_category_id']]);
    }

    /**
     * FAQ 카테고리 삭제
     */
    public function delete(Request $request, Response $response, string $faq_category_id, string $faq_id): Response
    {
        $faq = $this->service->getFaq($faq_id);

        $this->service->delete($faq['id']);

        return $response->withJson(['message' => 'FAQ 항목이 삭제되었습니다.']);
    }
}
