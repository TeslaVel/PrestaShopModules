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

public function editAction(Request $request, int $id = null)
{
    // Obtiene el token del controlador
    $token = $request->query->get('_token');
    $ctr_title = 'Formulario de registro de miembros';
    $action = $this->generateUrl('ps_dev_horizont_members_save', ['_token' => $token]);
    $ctr_submit_action = 'Crear';

    if ($id) {
      $ctr_title = 'Editar Miembro ';
      $ctr_submit_action = 'Actualizar';
      $action = $this->generateUrl('ps_dev_horizont_members_save', ['id' => $id, '_token' => $token]);
    }

    $form = $this->createFormBuilder()
    ->add('first_name', TextType::class)
    ->add('last_name', TextType::class)
    ->add('age', TextType::class)
    ->add('email', EmailType::class)
    ->setMethod('POST')
    ->setAction($action)
    ->getForm();

    if ($id) {
        $member = $this->getMemberById($id);
        $form->setData($member);
    }

    return $this->render('@Modules/devhorizontmembers/views/templates/admin/memberform.html.twig',
      [
        'ctr_title' => $ctr_title,
        'ctr_submit_action' => $ctr_submit_action,
        'form' => $form->createView()
      ]
    );
}

  public function saveAction(Request $request, int $id = null)
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

          if (filter_var($id, FILTER_VALIDATE_INT)) {
            $affected = $this->updateData($toInsert, $id);
            if ($affected) {
              $flash_object = $this->trans('Member was updated', 'Admin.Notifications.Success');
            }
          } else {
            $last_id = $this->insertData($toInsert);

            if (!filter_var($last_id, FILTER_VALIDATE_INT)) {
              $flash_type = 'error';
              $flash_object = $this->trans('Member could not be created', 'Admin.Notifications.Error');
            }
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

    $route = $flash_type == 'error' ? 'ps_dev_horizont_members_edit' : 'ps_dev_horizont_members';

    return $this->redirectToRoute($route);
  }

  public function deleteAction(Request $request, $id)
  {
    $member = $this->getMemberById($id);

    if (!empty($member)) {
      $affected = $this->deleteMember($id);

      if ($affected) {
        $this->addFlash('success', 'The member was deleted successfully.');
      } else {
        $this->addFlash('warning', 'The member was not deleted.');
      }
    }

    return $this->redirectToRoute('ps_dev_horizont_members');
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

  protected function updateData($data, $member_id) {
    $email = $data['email'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $age = $data['age'];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` SET `email` = "' . pSQL($email) . '", `first_name` = "' . pSQL($first_name) . '", `last_name` = "' . pSQL($last_name) . '", `age` = ' . (int)$age . ' WHERE `dev_horizont_member_id` = ' . (int)$member_id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

  protected function getAllMembers()
  {
    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'`';

    $results = Db::getInstance()->executeS($sql);

    return $results;
  }

  protected function getMemberById($id)
  {
    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `dev_horizont_member_id` = ' . (int)$id;

    $results = Db::getInstance()->executeS($sql);

    if (!empty($results)) {
      return $results[0];
    }

    return null;
  }

  public function deleteMember($id)
  {
    $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `dev_horizont_member_id` = ' . (int)$id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }
}
