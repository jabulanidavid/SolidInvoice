<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) 2013-2017 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InvoiceBundle\Tests\Form\Handler;

use Mockery as M;
use Money\Currency;
use SolidInvoice\CoreBundle\Response\FlashResponse;
use SolidInvoice\CoreBundle\Templating\Template;
use SolidInvoice\FormBundle\Test\FormHandlerTestCase;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Form\Handler\InvoiceCreateHandler;
use SolidInvoice\InvoiceBundle\Listener\WorkFlowSubscriber;
use SolidInvoice\InvoiceBundle\Model\Graph;
use SolidInvoice\MoneyBundle\Entity\Money;
use SolidInvoice\NotificationBundle\Notification\NotificationManager;
use SolidWorx\FormHandler\FormRequest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

class InvoiceCreateHandlerTest extends FormHandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Money::setBaseCurrency('USD');
    }

    public function getHandler()
    {
        $dispatcher = new EventDispatcher();
        $notification = M::mock(NotificationManager::class);
        $notification->shouldReceive('sendNotification')
            ->zeroOrMoreTimes();

        $dispatcher->addSubscriber(new WorkFlowSubscriber($this->registry, $notification));
        $stateMachine = new StateMachine(
            new Definition(
                ['new', 'draft'],
                [new Transition('new', 'new', 'draft')]
            ),
            new MethodMarkingStore(true, 'status'),
            $dispatcher,
            'invoice'
        );

        $router = M::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturn('/invoices/1');

        $handler = new InvoiceCreateHandler($stateMachine, $router);
        $handler->setDoctrine($this->registry);

        return $handler;
    }

    protected function assertOnSuccess(?Response $response, FormRequest $form, $invoice): void
    {
        /* @var Invoice $invoice */

        static::assertSame(Graph::STATUS_DRAFT, $invoice->getStatus());
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertInstanceOf(FlashResponse::class, $response);
        static::assertCount(1, $response->getFlash());
        static::assertCount(1, $this->em->getRepository(Invoice::class)->findAll());
    }

    protected function assertResponse(FormRequest $formRequest): void
    {
        static::assertInstanceOf(Template::class, $formRequest->getResponse());
    }

    protected function getHandlerOptions(): array
    {
        return [
            'invoice' => new Invoice(),
            'form_options' => [
                'currency' => new Currency('USD'),
            ],
        ];
    }

    public function getFormData(): array
    {
        return [
            'invoice' => [
                'discount' => [
                    'value' => 20,
                    'type' => 'percentage',
                ],
            ],
        ];
    }
}
