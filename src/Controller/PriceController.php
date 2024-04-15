<?php

namespace App\Controller;

use App\Api\Dto\PriceQueryDto;
use App\Exception\FormValidationException;
use App\Form\Type\PriceQueryType;
use App\Model\Price;
use App\Repository\DiscountRepository;
use App\Service\DiscountService;
use App\Service\FormUtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/price')]
class PriceController extends AbstractController
{
    /**
     * @throws FormValidationException
     */
    #[Route('', methods: ["GET"])]
    public function getPriceAction(
        Request $request,
        DiscountRepository $discountRepository,
        DiscountService $discountService,
        FormUtilsService $formUtilsService
    ): Price {
        /** @var PriceQueryDto $priceQueryDto */
        $priceQueryDto = $formUtilsService->getDtoForForm(PriceQueryType::class, $request->query->all());

        $discounts = $discountRepository->getActiveDiscounts();

        return $discountService->applyDiscounts($priceQueryDto, $discounts);
    }
}
