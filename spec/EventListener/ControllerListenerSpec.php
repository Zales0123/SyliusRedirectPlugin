<?php

declare(strict_types=1);

namespace spec\Setono\SyliusRedirectPlugin\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\SyliusRedirectPlugin\EventListener\ControllerListener;
use Setono\SyliusRedirectPlugin\Model\RedirectInterface;
use Setono\SyliusRedirectPlugin\Repository\RedirectRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListenerSpec extends ObjectBehavior
{
    function let(
        RedirectRepositoryInterface $redirectRepository,
        ObjectManager $objectManager
    ): void {
        $this->beConstructedWith($redirectRepository, $objectManager);
    }

    function it_is_a_controller_listener(): void
    {
        $this->shouldHaveType(ControllerListener::class);
    }

    function it_redirects_to_the_correct_controller(
        FilterControllerEvent $filterControllerEvent,
        Request $request,
        RedirectRepositoryInterface $redirectRepository,
        ObjectManager $objectManager,
        RedirectInterface $redirect
    ): void {
        $filterControllerEvent->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/home');

        $redirectRepository->findEnabledBySource('/home', false)->willReturn($redirect);
        $redirect->onAccess()->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $redirectRepository->findLastRedirect($redirect, false)->willReturn($redirect);
        $filterControllerEvent->setController(Argument::type(\Closure::class))->shouldBeCalled();

        $this->onKernelController($filterControllerEvent);
    }

    function it_does_not_redirect_if_there_is_no_enabed_redirect(
        FilterControllerEvent $filterControllerEvent,
        Request $request,
        RedirectRepositoryInterface $redirectRepository,
        ObjectManager $objectManager,
        RedirectInterface $redirect
    ): void {
        $filterControllerEvent->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/home');

        $redirectRepository->findEnabledBySource('/home', false)->willReturn($redirect);
        $redirect->onAccess()->shouldNotBeCalled();
    }
}
