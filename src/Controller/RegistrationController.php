<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager ,  MailerInterface $test ,  UrlGeneratorInterface $urlGenerator , MailerController $mailer,): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);//simplifie le traitement des données du formulaire 
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setVerificationcode(rand(100000, 999999));
            $user->setRole('USER');
            $user->setStatus(0);
            $entityManager->persist($user);
            $entityManager->flush();

            
            
                $activationCode = $user->getVerificationCode();
                $resetUrl = $urlGenerator->generate('app_activate_account', ['email' => $user->getEmail(), 'code' => $activationCode], UrlGeneratorInterface::ABSOLUTE_URL);
                // génère une URL complète pour l'activation du compte.
                // Send email with reset password URL
                $mailer->sendActivationEmail($test, $user->getEmail(), $resetUrl);//envoie un e-mail d'activation à l'utilisateur avec un lien pour activer son compte.
                $request->getSession()->getFlashBag()->add('success', 'Activation link has been sent to your email.');
                //dd($flashBag);
                return $this->redirectToRoute('app_login');
            
            /*return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );*/
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/activate/{email}/{code}', name: 'app_activate_account')]
    public function change(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, string $email, string $code, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $userRepository->findOneByEmail($email);
    
        if (!$user) {
            $request->getSession()->getFlashBag()->add('error', 'User not found!');

                            return $this->redirectToRoute('app_register');        }
    
        // Ensure the provided code matches the user's activation code
        if ($user->getVerificationCode() != $code) {
            $request->getSession()->getFlashBag()->add('error', 'Invalid activation code');

            return $this->redirectToRoute('app_register');  
        }
            $user->setStatus(1);
            $entityManager->persist($user);
            $entityManager->flush();
            $request->getSession()->getFlashBag()->add('success', 'Your account is Active now.');

            // Redirect to login page
            return $this->redirectToRoute('app_login');
        
    
    
    }
//taatiuser/form/kn form Valide et Submitted usr yaati mdp tsirlou hashage baad initialisation lil donner (code role w status)/génère une URL complète pour l'activation du compte.
// Send email with reset password URL/envoie un e-mail d'activation à l'utilisateur avec un lien pour activer son compte/verif lil code
}
