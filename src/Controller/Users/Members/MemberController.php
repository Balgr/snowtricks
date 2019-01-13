<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 13/01/2019
 * Time: 16:01
 */

namespace App\Controller\Users\Members;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/member") */
class MemberController extends Controller
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('member/index.html.twig', ['mainNavMember' => true, 'title' => 'Espace Membre']);
    }
}
