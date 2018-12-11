 <?php
namespace AppBundle\Controller\Backend\Louis21Debug;

use Doctrine\Common\Annotations\Annotation\Target;
use Louis21Bundle\Entity\Debug;
use SageBundle\Entity\Company;
use Louis21Bundle\Entity\Tenant;
use Louis21Bundle\Entity\TenantHistory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\Common\Annotations\DocLexer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use DateTime;
use Doctrine\ORM\EntityManager;



class Louis21Debug extends Controller
{
    /**
     * @Route("/debug" , name="debug")
     */
    public function indexAction()
    {

        //dump("hello debug");
        //die;
        //$debug= new Debug();
        $debug= $this->getDoctrine()->getManager()->getRepository('Louis21Bundle:Debug')->findAll();

        <button  class="fa fa-history"  onclick="{{ path('app_backend_louis21_debug_index') }}"></button>
            <button  class="fa fa-history"  onclick="location.href={{ path('app_backend_louis21_debug_executeagain', {'id': debug.id }) }}"></button>
        return $this->render('AppBundle:Backend/Louis21Debug:debug.html.twig', array(
            'debugs' => $debug,

        ));

    }


    /**
     * @Route("/thami/{id}" , name="thamitreat")
     */
    public function thamiAction($id, Debug $debug)
    {

        //dump("hello debug");
        //die;
        //$debug= new Debug();
        if($debug->getmodule)

        return $this->render('AppBundle:Backend/Louis21Debug:debug.html.twig', array(
            'debugs' => $debug,

        ));

    }


}
