<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 13/01/2019
 * Time: 15:27
 */

namespace App\Controller;

use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    private $errors;
    private $infos;

    public function __construct()
    {
        $this->errors = array();
        $this->infos = array();
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        if (!empty($authenticationUtils->getLastAuthenticationError())) {
            $this->errors[] = $authenticationUtils->getLastAuthenticationError();
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->get('form.factory')
            ->createNamedBuilder(null)
            ->add('_username', null, ['label' => 'Email'])
            ->add('_password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, ['label' => 'Mot de passe'])
            ->add('ok', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Connexion', 'attr' => ['class' => 'btn-primary btn-block']])
            ->getForm();

        return $this->render('security/login.html.twig', [
            'mainNavLogin' => true, 'title' => 'Connexion',
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'errors' => $this->errors,
            'infos' => $this->infos
        ]);
    }

    /**
     * @Route("/recuperation_mot_de_passe", name="recup_password")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return
     */
    public function forgottenPassword(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        if (!empty($authenticationUtils->getLastAuthenticationError())) {
            $this->errors[] = $authenticationUtils->getLastAuthenticationError();
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Creates the form
        $form = $this->get('form.factory')
            ->createNamedBuilder(null)
            ->add('email', null, ['label' => 'Email'])
            ->add('ok', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Réinitialiser', 'attr' => ['class' => 'btn-primary btn-block']])
            ->getForm();

        $form->handleRequest($request);

        // If the form has already been submitted without errors...
        if ($form->isSubmitted() && $form->isValid()) {
            $token = $this->createResetToken($form->getData()['email']);

            if ($token !== false) {
                // TODO : actually sends the email
                //var_dump($token);
                return $this->redirectToRoute('reset_password', array('token' => $token));
            }

            $this->infos[] = 'Merci. Si l\'email correspond à un utilisateur inscrit, vous recevrez un mail contenant un lien pour réinitialiser votre mot de passe.';
        }

        return $this->render('security/forgotten_password.html.twig', [
            'mainNavLogin' => true, 'title' => 'Réinitialiser le mot de passe',
            //
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'errors' => $this->errors,
            'infos' => $this->infos
        ]);

    }

    private function createResetToken($email)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Saves the token and its expiry date to the database
        $token = bin2hex(openssl_random_pseudo_bytes(32));
        $user->setResetToken($token);

        $date = new DateTime();
        $expiryDate = $date->modify('+1 day');
        $user->setTokenExpiryDate($expiryDate);

        $entityManager->flush();

        return $token;
    }

    /**
     * @Route("/recuperation_mot_de_passe/{token}", name="reset_password")
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, $token)
    {
        // Handles the form
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findByToken($token);

        if (!$user) {
            $this->errors[] = 'Token invalide. Veuillez recommencer.';
            return $this->redirectToRoute('recup_password');
        } else {
            // Check if the token is not expired
            $date = new \DateTime();
            if ($user->getTokenExpiryDate() < $date) {
                $this->errors[] = 'Token expiré. Veuillez recommencer.';
                return $this->redirectToRoute('recup_password');
            }
        }

        // Else, the token is valid and not expired :
        // Creates the form
        $form = $this->get('form.factory')
            ->createNamedBuilder(null)
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmation du mot de passe'),
            ))
            ->add('ok', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Réinitialiser', 'attr' => ['class' => 'btn-primary btn-block']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Checks if the submitted email corresponds to the User's
            if($user->getEmail() === $form->getData()['email']) {
                // Set the User's new password
                $user->setPlainPassword($form->getData()['plainPassword']);
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                // Deletes the token and expiry date
                $user->setResetToken(null);
                $user->setTokenExpiryDate(null);

                $entityManager->flush();
                return $this->redirectToRoute('login');
            }

            $this->errors[] = 'E-mail incorrect';
        }

        return $this->render('security/reset_password.html.twig', [
            'mainNavLogin' => true, 'title' => 'Réinitialiser le mot de passe',
            'form' => $form->createView(),
            'errors' => $this->errors,
            'infos' => $this->infos
        ]);
    }
}
