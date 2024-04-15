<?php

namespace App\Repository;

use App\Model\Discount;
use App\Model\DiscountCategory;
use App\Model\DiscountCondition\AgeGreaterThanOrEqual;
use App\Model\DiscountCondition\AgeLessThan;
use App\Model\DiscountCondition\PaidDateLessThanOrEqual;
use App\Model\DiscountCondition\PaidMonthEqual;
use App\Model\DiscountCondition\StartDateGreaterThanOrEqual;
use App\Model\DiscountCondition\StartDateLessThanOrEqual;

class DiscountRepository
{
    /**
     * Stub method for test task
     *
     * @psalm-return Discount[]
     */
    public function getActiveDiscounts(): array
    {
        $kidsDiscounts = new DiscountCategory(1, 'Детская скидка', 100, 'Скидка на ребенка до 18 лет');
        $earlyBookingDiscounts = new DiscountCategory(
            2,
            'Скидка за ранне бронирование',
            $kidsDiscounts->getPriority() - 1,
            'Рассчитывается от даты старта путешествия и даты его оплаты',
            '1500'
        );

        $activeDiscounts = [
            new Discount(
                '0.8',
                [new AgeGreaterThanOrEqual(3), new AgeLessThan(6)],
                $kidsDiscounts,
                'Скидка детям от 3 лет включительно до 6 лет',
            ),
            new Discount(
                '0.3',
                [new AgeGreaterThanOrEqual(6), new AgeLessThan(12)],
                $kidsDiscounts,
                'Скидка детям от 6 лет включительно до 12 лет',
                '4500'
            ),

            new Discount(
                '0.1',
                [new AgeGreaterThanOrEqual(12), new AgeLessThan(18)],
                $kidsDiscounts,
                'Скидка детям от 12 лет включительно до 18 лет'
            ),
            new Discount(
                '0.07',
                [
                    new PaidDateLessThanOrEqual('30 november this year'),
                    new StartDateGreaterThanOrEqual('1 april next year'),
                    new StartDateLessThanOrEqual('30 september next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 апреля по 30 сентября следующего года при оплате весь ноябрь текущего года',
            ),
            new Discount(
                '0.05',
                [
                    new PaidMonthEqual('december this year'),
                    new StartDateGreaterThanOrEqual('1 april next year'),
                    new StartDateLessThanOrEqual('30 september next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 апреля по 30 сентября следующего года при оплате весь декабрь текущего года',
            ),
            new Discount(
                '0.03',
                [
                    new PaidMonthEqual('january this year'),
                    new StartDateGreaterThanOrEqual('1 april this year'),
                    new StartDateLessThanOrEqual('30 september this year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 апреля по 30 сентября текущего года при оплате весь январь текущего года',
            ),

            new Discount(
                '0.07',
                [
                    new PaidDateLessThanOrEqual('march this year'),
                    new StartDateGreaterThanOrEqual('1 october this year'),
                    new StartDateLessThanOrEqual('14 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 октября текущего года по 14 января следующего года при оплате весь март текущего года',
            ),
            new Discount(
                '0.05',
                [
                    new PaidMonthEqual('april this year'),
                    new StartDateGreaterThanOrEqual('1 october this year'),
                    new StartDateLessThanOrEqual('14 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 октября текущего года по 14 января следующего года при оплате весь апрель текущего года',
            ),
            new Discount(
                '0.03',
                [
                    new PaidMonthEqual('may this year'),
                    new StartDateGreaterThanOrEqual('1 october this year'),
                    new StartDateLessThanOrEqual('14 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 1 октября текущего года по 14 января следующего года при оплате весь май текущего года',
            ),

            new Discount(
                '0.07',
                [
                    new PaidDateLessThanOrEqual('31 august this year'),
                    new StartDateGreaterThanOrEqual('15 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 15 января следующего года и далее при оплате весь август текущего года и ранее',
            ),
            new Discount(
                '0.05',
                [
                    new PaidMonthEqual('september this year'),
                    new StartDateGreaterThanOrEqual('15 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 15 января следующего года и далее при оплате весь сентябрь текущего года',
            ),
            new Discount(
                '0.03',
                [
                    new PaidMonthEqual('october this year'),
                    new StartDateGreaterThanOrEqual('15 january next year'),
                ],
                $earlyBookingDiscounts,
                'Скидка на путешествия с датой старта с 15 января следующего года и далее при оплате весь октябрь текущего года',
            ),
        ];

        usort(
            $activeDiscounts,
            fn (Discount $discount1, Discount $discount2) =>
                -1 * ($discount1->getCategory()->getPriority() <=> $discount2->getCategory()->getPriority())
        );

        return $activeDiscounts;
    }
}
