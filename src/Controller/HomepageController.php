<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 13/01/2019
 * Time: 15:31
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends Controller {

    /**
     * @Route("/")
     */
    public function index() {
        return $this->render('homepage/index.html.twig', ['mainNavHome'=>true, 'title'=>'Accueil']);
    }

}
