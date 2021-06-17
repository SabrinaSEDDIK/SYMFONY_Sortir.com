<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ChangePasswordType;
use App\Form\ForgottenPasswordType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if($error){
            $this->addFlash(
                'fail',
                "Identifiants incorrects !"
            );
            return $this->redirectToRoute('app_login');
        } else{
            return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        }
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/security/forgotten_password", name="forgotten_password")
     */
    public function forgottenPassword(Participant $user = null, EntityManagerInterface $manager, Request $request, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, ParticipantRepository $userRepository) : Response
    {
        /* On instancie le formulaire */
        $form = $this->createForm(ForgottenPasswordType::class);

        $form->handleRequest($request);

        $email = $form->get("emailResetPass")->getData();

        $user = $userRepository->findOneBy(["email"=> $email], []);

        if ($form->isSubmitted() && $form->isValid())
        {

            if($request->isMethod("POST"))
            {
                /* On génére un token unique */
                $token = $tokenGenerator->generateToken();
                try
                {
                    $user->setResetToken($token);
                    $manager->flush();
                }
                catch(\Exception $e)
                {
                    $this->addFlash("Warning", $e->getMessage());

                    return $this->redirectToRoute("app_login");
                }

                $url = $this->generateUrl("reset_password", array("token"=> $token), UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new Email())
                    ->from("projet.sortir.2021@gmail.com")
                    ->to($user->getEmail())
                    ->subject("Récupération de mot de passe test")
                    ->text("Voici le lien de récupération de votre mot de passe : ".$url, 'text/html')
                    ->html("<p>Ceci est un test : ".$url,'text/html'."</p>");

                $mailer->send($message);

                $this->addFlash("message", "Le mail de récupération de mot de passe à bien été envoyé");
            }

        }

        return $this->render("security/forgottenPassword.html.twig", [
            "form"=> $form->createView(),
            "titre"=> "Réinitialisation du mot de passe",
            "user"=> $user,
        ]);
    }

    /**
     * @Route("/resetPassword/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, EntityManagerInterface $manager, string $token, UserPasswordEncoderInterface $passwordEncoder, ParticipantRepository $userRepository)
    {
        $form = $this->createForm(ChangePasswordType::class);

        $form->handleRequest($request);


        /* redéfinir le user */

        $user = $userRepository->findOneBy(["resetToken"=> $token], []);

        if ($form->isSubmitted() && $form->isValid())
        {
            if($request->isMethod("POST"))
            {
                $em = $this->getDoctrine()->getManager();

                $user = $em->getRepository(Participant::class)->findOneByResetToken($token);

                $user->setResetToken(null);

                $newPassword = $form->get("plainPassword")->getData();

                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $newPassword)
                );

                $manager->flush();
                $this->addFlash("message", "Votre mot de passe a bien été réinitialiser");

                return $this->redirectToRoute('app_login');

            }
        }

        return $this->render("security/resetPassword.html.twig", [
            "form"=> $form->createView(),
            "title"=> "Réinitialisation du mot de passe",
            "user"=> $user
        ]);
    }
}
