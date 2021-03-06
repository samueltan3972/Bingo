<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Application\Model\IndexModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if(isset($_COOKIE['userID'])){
            return $this->redirect()->toUrl('/application/play');
        }

        if(isset($_COOKIE['ErrorMessage']))
            $message = $_COOKIE['ErrorMessage'];
        else
            $message = '';

        $view = new ViewModel(array(
            'message' => $message,
        ));
        $view->setTemplate('application/index/index');

        return $view;
    }

    public function playAction()
    {
        if(empty($_COOKIE['userID'])){
            return $this->redirect()->toUrl('/');
        }

        $result = $this->getHistoryAction();

        $view = new ViewModel(array(
            'score' => $result['score'],
        ));
        $view->setTemplate('application/index/play');

        return $view;
    }

    public function loginAction()
    {
        $username = $this->params()->fromPost('username', '');
        $password = $this->params()->fromPost('password', '');

    	$model = new IndexModel();
    	$result = $model->login($username, $password);

        if($result['status'] == 'ok'){
            setcookie('userID', $result['userID'], time() + 3600, '/');
            setcookie('username', $result['username'], time() + 3600, '/');

            // delete cookie error message
            unset($_COOKIE['ErrorMessage']);
            setcookie('ErrorMessage', '', time() - 3600);

            return $this->redirect()->toUrl('/application/play');
        }
        else{
            setcookie('ErrorMessage', $result['Error message'], time() + 60, '/');

            return $this->redirect()->toUrl('/');
        }
    }

     public function registerAction()
    {
        $username = $this->params()->fromPost('username', '');
        $password = $this->params()->fromPost('password', '');

        $model = new IndexModel();
        $result = $model->register($username, $password);

        if($result['status'] == 'ok'){
            setcookie('userID', $result['userID'], time() + 3600, '/');
            setcookie('username', $result['username'], time() + 3600, '/');

            // delete cookie erroe message
            unset($_COOKIE['ErrorMessage']);
            setcookie('ErrorMessage', '', time() - 3600);

            return $this->redirect()->toUrl('/application/play');
        }
        else{
            setcookie('ErrorMessage', $result['Error message'], time() + 60, '/');

            return $this->redirect()->toUrl('/');
        }
    }

     public function changePasswordAction()
    {   
        if ($this->getRequest()->isXmlHttpRequest()) {
            $userID = $_COOKIE['userID'];
            $oldPassword = $this->params()->fromPost('oldPassword', '');
            $newPassword = $this->params()->fromPost('newPassword', '');

            $model = new IndexModel();
            $result = $model->changePassword($userID, $oldPassword, $newPassword);

                echo json_encode($result);
                exit(); 
        } else {
            echo 'Access denied: Not ajax';
            exit();
        }
    }

    public function insertGameScoreAction(){
        if(empty($_COOKIE['userID'])){
            return $this->redirect()->toUrl('/');
        }

        $userID = $_COOKIE['userID'];

        $model = new IndexModel();
        $score = $_GET['score'];
        
        $result = $model->insertGameScore($userID, $score);

        if($result['status'] != 'ok'){
            echo "Error: ".$result['Error message'];
            sleep(2);
        }

        return $this->redirect()->toUrl('/application/gameOver');
    }

     public function getHistoryAction()
    {
        $userID = $_COOKIE['userID'];

        $model = new IndexModel();
        $result = $model->getHistory($userID);

        return $result;
    }

    public function gameAction(){
        if(empty($_COOKIE['userID'])){
            return $this->redirect()->toUrl('/');
        }

        $view = new ViewModel();
        $view->setTemplate('application/index/game');

        return $view;
    }

    public function gameOverAction(){
        if(empty($_COOKIE['userID'])){
            return $this->redirect()->toUrl('/');
        }

        $view = new ViewModel();
        $view->setTemplate('application/index/gameOver');

        return $view;
    }

    public function logoutAction(){
        $past = time() - 3600;
        foreach ($_COOKIE as $key => $value )
        {
            setcookie( $key, $value, $past, '/' );
        }

        return $this->redirect()->toUrl('/');
    }


}
	