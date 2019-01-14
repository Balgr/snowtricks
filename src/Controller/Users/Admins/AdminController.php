<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 14/01/2019
 * Time: 00:14
 */

namespace App\Controller\Users\Admins;

use App\Controller\Users\Members\MemberController;
use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $errors = [];
    private $infos = [];
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', ['mainNavMember' => true, 'title' => 'Espace Admin']);
    }

    /**
     * @Route("/users", name="list_users")
     */
    public function listUsers() {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->getAll();

        return $this->render('admin/users/list_users.html.twig', [
            'users' => $users,
            'mainNavAdmin' => true,
            'title' => 'Liste d\'utilisateurs',
            'infos' => $this->infos,
            'errors' => $this->errors
        ]);
    }

    /**
     * @Route("/users/{id}", name="edit_user", requirements={"id"="\d+"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param $id
     * @return
     */
    public function editUser(Request $request,UserPasswordEncoderInterface $passwordEncoder, $id) {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->find($id);

        if(!$user) {
            $this->errors[] = 'L\'utilisateur #$id n\'existe pas';
            $this->redirectToRoute('list_users');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setIsActive(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // TODO : if the current User is the one under edition, do NOT log the User out

            $this->infos[] = 'Utilisateur modifié';
            return $this->redirectToRoute('list_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'mainNavRegistration' => true,
            'title' => 'Edition d\'un utilisateur']);
    }

    /**
     * @Route("/users/add", name="add_user")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return
     */
    public function addUser(Request $request,UserPasswordEncoderInterface $passwordEncoder) {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setIsActive(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->infos[] = 'Utilisateur ajouté';
            return $this->redirectToRoute('list_users');
        }

        return $this->render('admin/users/add.html.twig', [
            'form' => $form->createView(),
            'errors' => $this->errors,
            'infos' => $this->infos,
            'mainNavRegistration' => true,
            'title' => 'Nouvel utilisateur'
        ]);
    }
    /**
     * @Route("/users/delete/{id}", name="delete_user", requirements={"id"="\d+"})
     */
    public function removeUser($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if(!$user) {
            $this->errors[] = 'L\'utilisateur #$id n\'existe pas';
            $this->redirectToRoute('list_users');
        }
        $em->remove($user);
        $em->flush();

        if($em->getRepository(User::class)->find($id) === null) {
            $this->infos[] = 'Utilisateur supprimé';
        } else {
            $this->errors[] = 'Une erreur s\'est produite';
        }

        //return $this->redirectToRoute('list_users');
        return $this->listUsers();
    }

}
