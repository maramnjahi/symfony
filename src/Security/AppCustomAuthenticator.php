<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator ,  private UserProviderInterface $userProvider)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');
        if (empty($email) || empty($password) || empty($csrfToken)) {
            // Throw an authentication exception indicating bad request
            $request->getSession()->getFlashBag()->add('error', 'all fields are required');
        }
        $user = $this->userProvider->loadUserByIdentifier($email);
        if (!$user || !$user->isStatus()) {
            // Throw an authentication exception indicating inactive user
            throw new AuthenticationException('User is not active.');
        }else{
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }
}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Check if the user has ROLE_ADMIN
        if ($token->getUser() && in_array('ADMIN', $token->getUser()->getRoles())) {
            // Redirect admin users to the admin dashboard
            return new  RedirectResponse ($this->urlGenerator->generate('app_admin'));
        }
        
        // Check if the user has ROLE_USER
        if ($token->getUser() && in_array('USER', $token->getUser()->getRoles())) {
            // Redirect user users to the user dashboard
            return new  RedirectResponse ($this->urlGenerator->generate('home'));
        }

        // If no specific role matches, you can redirect to a default route
        return new  RedirectResponse ($this->urlGenerator->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Customize the response for authentication failure
        if ($request->getSession()->getFlashBag()->has('error')) {
            // If form errors exist, redirect back to login page
            return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
        }
    
        // Otherwise, proceed with default behavior (display "Invalid credentials" error message)
        return parent::onAuthenticationFailure($request, $exception);
    }
}
