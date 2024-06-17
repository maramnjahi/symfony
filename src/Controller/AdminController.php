<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index( UserRepository $userRepository): Response
    {
        if ($this->getUser()  && in_array('ADMIN', $this->getUser()->getRoles())){
            $userCounts = [];

            $registrationDates = $userRepository->findAllRegistrationDates();
    
            $monthLabels = [];
    //extrait le nom complet du mois correspondant à cette date en utilisant format('F')
            foreach ($registrationDates as $date) {
                if ($date['date'] !== null) {
                // Extract month name from the registration date
                $monthLabels[] = $date['date']->format('F');
            }
            }
    
            $monthLabels = array_unique($monthLabels);//garantit que chaque mois apparaît uniquement une fois dans le tableau $monthLabels
            $auser =  count($userRepository->findByStatus(true));
            $iuser= count($userRepository->findByStatus(false));
            foreach ($monthLabels as $month) {
                $users = $userRepository->findByRegistrationMonth($month);
    
                // Initialize counter for active users
                $activeCount = 0;
                $inactive=0;
                // Loop through users to count active users
                foreach ($users as $user) {
                    if ($user->getStatus()) {
                        $activeCount++;
                       
                    }else{
                        $inactive ++;
                       
                    }
                }
    
                $activeUser[] = $activeCount;
            $inactiveUser[] = $inactive;
            }
            
            $sessionDir = ini_get('session.save_path');

            // Count session files
            $sessionFiles = glob($sessionDir . '/*');
            $sessionCount = count($sessionFiles);
            
        return $this->render('dashboard/index.html.twig', [
            'months' => array_values($monthLabels),
            'dataActive'=>$activeUser,
            'dataInactive'=>$inactiveUser,
            'session'=>$sessionCount,
            'Actuser'=>$auser,
            'Inuser'=>$iuser,
            'cnumber'=>count($userRepository->findByRole("CLIENT"))

        ]);
    }else{
        return $this->redirectToRoute('app_login');
    }

       /* */
    }
    #[Route('/admin/chartsadmin', name: 'chartsadmin')]
    public function charts() : Response
    {
        return $this->render('admin/charts/chartjs.html.twig');
    }
    #[Route('/admin/doc', name: 'doc')]
    public function doc() : Response
    {
        return $this->render('admin/documentation/documentation.html.twig');
    }
    #[Route('/admin/form', name: 'form')]
    public function form() : Response
    {
        return $this->render('admin/forms/basicelements.html.twig');
    }
    #[Route('/admin/icon', name: 'icon')]
    public function icon() : Response
    {
        return $this->render('admin/icons/mdi.html..twig');
    }
    #[Route('/admin/eror404', name: 'eror404')]
    public function eror404() : Response
    {
        return $this->render('admin/samples/error-404.html.twig');
    }
    #[Route('/admin/error-500', name: 'error500')]
    public function error500() : Response
    {
        return $this->render('admin/samples/error-500.html.twig');
    }
    #[Route('/admin/tables', name: 'tables')]
    public function tables() : Response
    {
        return $this->render('admin/tables/basictable.html.twig');
    }
    #[Route('/admin/button', name: 'button')]
    public function button() : Response
    {
        return $this->render('admin/ui-features/buttons.html.twig');
    }
    #[Route('/admin/dropdowns', name: 'dropdowns')]
    public function dropdowns() : Response
    {
        return $this->render('admin/ui-features/dropdowns.html.twig');
    }
    #[Route('/admin/typography', name: 'typography')]
    public function typography() : Response
    {
        return $this->render('admin/ui-features/typography.html.twig');
    }
}
