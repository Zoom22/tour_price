<?php

namespace App\Tests\App\Controller;

use App\Api\Dto\PriceQueryDto;
use App\Exception\FormValidationException;
use App\Model\Discount;
use App\Model\DiscountCategory;
use App\Model\DiscountCondition\AgeLessThan;
use App\Model\DiscountCondition\PaidMonthEqual;
use App\Model\Price;
use App\Service\DiscountService;
use App\Service\FormUtilsService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceControllerTest extends WebTestCase
{
    private ?KernelBrowser $client;

    public function testGetPriceActionInternalServerError(): void
    {
        self::assertNotNull($this->client);

        $discountServiceMock = $this->createMock(DiscountService::class);
        $discountServiceMock
            ->expects(self::once())
            ->method('applyDiscounts')
            ->willThrowException(new \Exception('test exception'));
        $this->client->getContainer()->set(DiscountService::class, $discountServiceMock);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price',
            [
                'basePrice' => 10000,
                'birthday'  => '2020-01-01',
            ],
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);
        self::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertFalse($content['success'] ?? null);
        self::assertEquals(
            'В настоящее время рассчитать стоимость путешествия не возможно. Попробуйте оправить запрос позже.',
            $content['errors'][0] ?? null
        );
    }

    public function testGetPriceActionFormValidationError(): void
    {
        self::assertNotNull($this->client);

        $formMock = $this->createMock(FormInterface::class);
        $formMock
            ->method('getErrors')
            ->willReturn(new FormErrorIterator(
                $formMock,
                [
                    new FormError('test form error'),
                ]
            ));
        $formValidationError = new FormValidationException($formMock);

        $formUtilsServiceMock = $this->createMock(FormUtilsService::class);
        $formUtilsServiceMock
            ->expects(self::once())
            ->method('getDtoForForm')
            ->willThrowException($formValidationError);
        $this->client->getContainer()->set(FormUtilsService::class, $formUtilsServiceMock);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price'
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertFalse($content['success'] ?? null);
        self::assertEquals(
            'test form error',
            $content['errors'][0] ?? null
        );
    }

    public function testGetPriceActionSuccess(): void
    {
        self::assertNotNull($this->client);


        $price = new Price('5000');
        $discount1 = new Discount(
            (string)(1 / rand(1, 100)),
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );
        $discount2 = new Discount(
            (string)(1 / rand(1, 100)),
            [new PaidMonthEqual(uniqid())],
            new DiscountCategory(2, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        $expectedDiscountsDescriptions = [$discount1->getDescription(), $discount2->getDescription()];

        $price->addAppliedDiscount($discount1);
        $price->addAppliedDiscount($discount2);

        $discountServiceMock = $this->createMock(DiscountService::class);
        $discountServiceMock
            ->expects(self::once())
            ->method('applyDiscounts')
            ->with(
                self::callback(function ($arg) {
                    return $arg instanceof PriceQueryDto
                        && $arg->getBasePrice() === '10000';
                }),
                self::callback(function ($arg) {
                    if (!is_array($arg)) {
                        return false;
                    }

                    foreach ($arg as $discount) {
                        if (!($discount instanceof Discount)) {
                            return false;
                        }
                    }

                    return true;
                }),
            )
            ->willReturn($price);
        $this->client->getContainer()->set(DiscountService::class, $discountServiceMock);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price',
            [
                'basePrice' => 10000,
                'birthday'  => '2020-01-01',
            ],
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($content['success'] ?? null);
        self::assertEquals($content['price'] ?? null, (float)$price->getPrice());

        self::assertCount(2, $content['discounts'] ?? []);
        foreach ($content['discounts'] ?? [] as $discountDescription) {
            self::assertContains($discountDescription, $expectedDiscountsDescriptions);
        }
    }

    public function testGetPriceActionRealCaseKid4YearsOldAndEarlyBooking(): void
    {
        self::assertNotNull($this->client);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price',
            [
                'basePrice' => 10000,
                'birthday'  => '2023-01-01',
                'startDate' => '2027-05-01',
                'paidDate'  => '2026-11-20',
            ],
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($content['success'] ?? null);
        self::assertEquals(1860, $content['price'] ?? null);

        self::assertCount(2, $content['discounts'] ?? []);
        foreach ($content['discounts'] ?? [] as $discountDescription) {
            self::assertContains($discountDescription, [
                'Скидка детям от 3 лет включительно до 6 лет',
                'Скидка на путешествия с датой старта с 1 апреля по 30 сентября следующего года при оплате весь ноябрь текущего года',
            ]);
        }
    }

    public function testGetPriceActionRealCaseKid7YearsOld(): void
    {
        self::assertNotNull($this->client);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price',
            [
                'basePrice' => 100000,
                'birthday'  => '2021-01-01',
                'startDate' => '2027-05-01',
                'paidDate'  => '2027-01-16',
            ],
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($content['success'] ?? null);
        self::assertEquals(94000, $content['price'] ?? null);

        self::assertCount(2, $content['discounts'] ?? []);
        foreach ($content['discounts'] ?? [] as $discountDescription) {
            self::assertContains($discountDescription, [
                'Скидка детям от 6 лет включительно до 12 лет',
                'Скидка на путешествия с датой старта с 1 апреля по 30 сентября текущего года при оплате весь январь текущего года',
            ]);
        }
    }

    public function testGetPriceActionRealCaseKid13YearsOld(): void
    {
        self::assertNotNull($this->client);

        $this->client->request(
            Request::METHOD_GET,
            '/api/price',
            [
                'basePrice' => 10000,
                'birthday'  => '2011-01-01',
                'startDate' => '2027-01-15',
                'paidDate'  => '2026-09-30',
            ],
        );
        $response = $this->client->getResponse();
        $content = json_decode((string)$response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($content['success'] ?? null);
        self::assertEquals(8550, $content['price'] ?? null);

        self::assertCount(2, $content['discounts'] ?? []);
        foreach ($content['discounts'] ?? [] as $discountDescription) {
            self::assertContains($discountDescription, [
                'Скидка детям от 12 лет включительно до 18 лет',
                'Скидка на путешествия с датой старта с 15 января следующего года и далее при оплате весь сентябрь текущего года',
            ]);
        }
    }

    protected function tearDown(): void
    {
        $this->client = null;

        parent::tearDown();
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['debug' => true]);
        $this->client->disableReboot();
    }
}
