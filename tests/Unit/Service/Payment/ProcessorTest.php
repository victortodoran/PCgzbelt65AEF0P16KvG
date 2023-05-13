<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Payment;

use App\DTO\Payment\PaymentDTO;
use App\Service\Payment\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessorTest extends TestCase
{
    private MockObject&ConstraintViolationListInterface $constraintViolationList;
    private Processor $paymentProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $validator = $this->createMock(ValidatorInterface::class);
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $validator->method('validate')->willReturn($this->constraintViolationList);

        $this->paymentProcessor = new Processor($validator);
    }

    /**
     * @dataProvider ccvDataProvider
     */
    public function testProcessPaymentFailsWithInvalidInput(string $cvv): void
    {
        $this->constraintViolationList->method('count')->willReturn(1);
        $this->assertFalse($this->paymentProcessor->processPayment($this->createPaymentDTO($cvv)));
    }

    /**
     * @dataProvider ccvDataProvider
     */
    public function testProcessPayment(string $cvv): void
    {
        $this->assertSame(
            str_starts_with($cvv, '1'),
            $this->paymentProcessor->processPayment($this->createPaymentDTO($cvv))
        );
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function ccvDataProvider(): array
    {
        return [
            'valid' => ['ccv' => '111'],
            'invalid' => ['ccv' => '222'],
        ];
    }

    private function createPaymentDTO(string $ccv): PaymentDTO
    {
        return new PaymentDTO(
            'Victor',
            '4444111122223333',
            $ccv,
            100
        );
    }
}
