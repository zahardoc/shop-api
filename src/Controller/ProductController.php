<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends BaseController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct( ProductRepository $productRepository )
    {

        $this->productRepository = $productRepository;
    }


    /**
     * @Route("/products", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     */
    public function newAction(Request $request, EntityManagerInterface $em)
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $this->processForm($request, $form);

        if(!$form->isValid()){
            throw new \Exception('Invalid data sent', Response::HTTP_BAD_REQUEST);
        }

        $em->persist($product);

        $em->flush();

        $url = $this->generateUrl('show_product', ['barcode' => $product->getBarcode()]);

        $response = new JsonResponse($this->serialize($product), Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }


    /**
     * @Route("/products/{barcode}", name="show_product", methods={"GET"})
     * @param string $barcode
     * @return JsonResponse
     */
    public function showAction($barcode)
    {
        $product = $this->productRepository->findOneBy(['barcode' => $barcode]);

        return new JsonResponse($this->serialize($product));
    }


    /**
     * @param Product $product
     * @return array
     */
    protected function serialize(Product $product)
    {
        return [
            'name' => $product->getName(),
            'barcode' => $product->getBarcode(),
            'cost' => $product->getCost(),
            'vat' => $product->getVatClass()->getPercent(),
        ];
    }

}
