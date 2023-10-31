<?php
declare(strict_types=1);

namespace PrestaShop\Module\DevHorizontMembers\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Db;

class MemberController extends FrameworkBundleAdminController
{
  const TAB_CLASS_NAME = 'AdminMembers';
  const DB_TABLE_NAME = 'dev_horizont_members';

  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
   * @return Response
   */
  public function indexAction(Request $request)
  {
    $ctr_title = 'Listado de Miembros';
    $token = $request->query->get('_token');

    $members = $this->getAllMembers();

    return $this->render('@Modules/devhorizontmembers/views/templates/admin/memberslist.html.twig',
      [
        'ctr_title' => $ctr_title,
        'token' => $token,
        'ctr_content' => '',
        'members' => $members
      ]
    );
  }

  public function newAction(Request $request)
  {
    $ctr_title = 'Formulario de registro de miembros';
  
    // Obtiene el token del controlador
    $token = $request->query->get('_token');
    $form = $this->createFormBuilder()
    ->add('first_name', TextType::class)
    ->add('last_name', TextType::class)
    ->add('age', TextType::class)
    ->add('email', EmailType::class)
    ->setMethod('POST')
    ->setAction($this->generateUrl('ps_dev_horizont_members_save', ['_token' => $token]))
    ->getForm();

    // $request->set('form', $form);

    return $this->render('@Modules/devhorizontmembers/views/templates/admin/memberform.html.twig',
      [
        'ctr_title' => $ctr_title,
        'form' => $form->createView()
      ]
    );
  }

  public function saveAction(Request $request)
  {
    // $data = $request->get('form');
    $form = $this->createFormBuilder()
    ->add('first_name', TextType::class)
    ->add('last_name', TextType::class)
    ->add('age', TextType::class)
    ->add('email', EmailType::class)
    ->getForm();
    $form->handleRequest($request);

  
    $flash_type = 'success';
    $flash_object = $this->trans('Member was created', 'Admin.Notifications.Success');
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();

      if (
        !empty($data['first_name']) &&
        !empty($data['last_name']) &&
        !empty($data['age']) &&
        !empty($data['email'])
      )
      {
        $toInsert = [
          'first_name' => $data['first_name'],
          'last_name' => $data['last_name'],
          'age' => $data['age'],
          'email' => $data['email']
        ];

        try {
          $last_id = $this->insertData($toInsert);

          if (!filter_var($last_id, FILTER_VALIDATE_INT)) {
            $flash_type = 'error';
            $flash_object = $this->trans('Member could not be created', 'Admin.Notifications.Error');
          }
        } catch (SupplierException $e) {
          $flash_type = 'error';
          $flash_object = $this->getErrorMessageForException($e, $this->getErrorMessages());
        }

      } else {
        $flash_type = 'error';
        $flash_object = $this->trans('There are missing values', 'Admin.Notifications.Error');
      }

      $this->addFlash($flash_type, $flash_object);
    }

    $route = $flash_type == 'error' ? 'ps_dev_horizont_members_new' : 'ps_dev_horizont_members';

    return $this->redirectToRoute($route);
  }

  protected function insertData($data) {
    $email = $data['email'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $age = $data['age'];


    $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'` (`email`, `first_name`, `last_name`, `age`) VALUES ("'.pSQL($email). '", "' .pSQL($first_name). '", "' .pSQL($last_name). '", ' .(int)$age.')';

    Db::getInstance()->execute($sql);

    $id = (int)Db::getInstance()->Insert_ID();
    return $id;
  }

  protected function getAllMembers()
  {
    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'`';

    $results = Db::getInstance()->executeS($sql);

    return $results;
  }
}
