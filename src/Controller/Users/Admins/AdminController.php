<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 14/01/2019
 * Time: 00:14
 */

namespace App\Controller\Users\Admins;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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


}
