<?php

namespace App\Faq;

use App\Faq\FaqService;
use App\Faq\Model\FaqCategoryRequest;
use App\Faq\Model\FaqCategorySearchRequest;
use App\Faq\Model\FaqRequest;
use Core\Model\PageParameters;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class FaqController
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
    public function index(Request $request, Response $response): Response
    {
        $query_params = $request->getQueryParams();
        $request_params = new FaqCategorySearchRequest($query_params);
        $page_params = new PageParameters($query_params);
        $params = array_merge($request_params->toArray(), $page_params->toArray());

        $faq_categories = $this->service->getFaqCategories($params);

        $response_data = [
            "faq_categories" => $faq_categories,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/faq_category_list.html', $response_data);
    }

    /**
     * FAQ 카테고리 등록
     */
    public function insertCategory(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $data = new FaqCategoryRequest($request_body);

        $this->service->insertCategory($data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.faq');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * FAQ 카테고리 수정
     */
    public function updateCategory(Request $request, Response $response, array $args): Response
    {
        $faq_category = $this->service->getFaqCategory($args['faq_category_id']);
        $request_body = $request->getParsedBody();
        $data = new FaqCategoryRequest($request_body);

        $this->service->updateCategory($faq_category['id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.faq');
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * FAQ 카테고리 삭제
     */
    public function deleteCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $faq_category = $this->service->getFaqCategory($args['faq_category_id']);

            $this->service->deleteFaqs($faq_category['id']);
            $this->service->deleteCategory($faq_category['id']);

            return api_response_json($response, [
                'result' => 'success',
                'message' => 'FAQ 카테고리가 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * FAQ 항목 목록 페이지
     */
    public function list(Request $request, Response $response, array $args): Response
    {
        $faq_category = $this->service->getFaqCategory($args['faq_category_id']);
        $faqs = $this->service->getFaqs($args['faq_category_id']);

        $response_data = [
            "faq_category" => $faq_category,
            "faqs" => $faqs,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/faq_list.html', $response_data);
    }

    /**
     * FAQ 항목 등록 폼 페이지
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        $faq_category = $this->service->getFaqCategory($args['faq_category_id']);

        $response_data = [
            "faq_category" => $faq_category,
        ];
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/faq_form.html', $response_data);
    }

    /**
     * FAQ 항목 등록
     */
    public function insert(Request $request, Response $response, array $args): Response
    {
        $request_body = $request->getParsedBody();
        $data = new FaqRequest($request_body);

        $this->service->insert($args['faq_category_id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.faq.list', ['faq_category_id' => $args['faq_category_id']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * FAQ 항목 상세 폼 페이지
     */
    public function view(Request $request, Response $response, array $args): Response
    {
        $faq = $this->service->getFaq($args['id']);
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
    public function update(Request $request, Response $response, array $args): Response
    {
        $faq = $this->service->getFaq($args['id']);
        $request_body = $request->getParsedBody();
        $data = new FaqRequest($request_body);

        $this->service->update($faq['id'], $data->toArray());

        $routeContext = RouteContext::fromRequest($request);
        $redirect_url = $routeContext->getRouteParser()->urlFor('admin.content.faq.list', ['faq_category_id' => $faq['faq_category_id']]);
        return $response->withHeader('Location', $redirect_url)->withStatus(302);
    }

    /**
     * FAQ 카테고리 삭제
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $faq = $this->service->getFaq($args['id']);

            $this->service->delete($faq['id']);

            return api_response_json($response, [
                'result' => 'success',
                'message' => 'FAQ 항목이 삭제되었습니다.',
            ], 200);
        } catch (\Exception $e) {
            return api_response_json($response, [
                'result' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
