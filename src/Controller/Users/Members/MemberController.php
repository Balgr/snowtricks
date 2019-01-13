<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 13/01/2019
 * Time: 16:01
 */

namespace App\Controller\Users\Members;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    private $errors = [];
    private $infos = [];
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('member/index.html.twig', ['mainNavMember' => true, 'title' => 'Espace Membre']);
    }


    /**
     * @Route("/profile")
     */
    public function profile(Request $request) {
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->get('email')->setData($user->getEmail());

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->infos[] = 'Modification effectuée';
        }


        return $this->render('member/profile.html.twig', [
            'form' => $form->createView(),
            'mainNavMember' => true,
            'title' => 'Espace Membre',
            'infos' => $this->infos,
            'errors' => $this->errors
            ]);
    }
}
