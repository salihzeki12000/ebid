<?php
namespace ebid\Controller;
use Symfony\Component\HttpFoundation\Response;
use ebid\Entity\User;
use ebid\Entity\Result;
use ebid\Event\RegisterEvent;

/**
 *
 * @author yanwsh
 *        
 */
class UserController extends baseController
{
    function registerAction(){
        global $encoderFactory;
        $MySQLParser = $this->container->get('MySQLParser');
        $data = json_decode($this->request->getContent());
        $user = new User(0, $data->username);
        if(!$user->isValid($data)){
            $result = new Result(Result::FAILURE, 'post data is not valid.');
            return new Response(json_encode($result));
        }
        $user->set($data);
        $arr = $MySQLParser->select($user, "username = '{$user->username}'");
        if(count($arr) != 0){
            $result = new Result(Result::FAILURE, 'username is duplicate, please input another username.');
        }else{
            $encoder = $encoderFactory->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->password = $password;
            $user->roles = 'ROLE_USER';

            $registerEvent = new RegisterEvent($user);
            $this->dispatcher->dispatch(RegisterEvent::REGISTER, $registerEvent);

            $MySQLParser->insert($user, array('uid'));


            $result = new Result(Result::SUCCESS, 'register successfully.');
        }
        return new Response(json_encode($result));
    }
}

?>