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
use PrestaShop\Module\DevHorizontMembers\Functions\DevHzMembersDbFunctions\DevHzSql;

class MemberController extends FrameworkBundleAdminController
{
  const TAB_CLASS_NAME = 'AdminMembers';
  const DB_TABLE_NAME = DevHzSql::DB_TABLE_NAME;

  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
   * @return Response
   */
  public function indexAction(Request $request)
  {
    $ctr_title = 'Listado de Miembros';
    $token = $request->query->get('_token');

    $DevHzSql = new DevHzSql();

    $members = $DevHzSql->getAllMembers();

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
      $token = $request->query->get('_token');
      $ctr_title = 'Member Registration Form';
      $action = $this->generateUrl('ps_dev_horizont_members_save', ['_token' => $token]);
      $ctr_submit_action = 'Create';

      if ($id) {
        $ctr_title = 'Edit Member ';
        $ctr_submit_action = 'Update';
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
          $DevHzSql = new DevHzSql();
          $member = $DevHzSql->getMemberById($id);
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
          $DevHzSql = new DevHzSql();

          if (filter_var($id, FILTER_VALIDATE_INT)) {
            $affected = $DevHzSql->updateData($toInsert, $id);
            if ($affected) {
              $flash_object = $this->trans('Member was updated', 'Admin.Notifications.Success');
            }
          } else {
            $last_id = $DevHzSql->insertData($toInsert);

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
    $DevHzSql = new DevHzSql();
    $member = $DevHzSql->getMemberById($id);

    if (!empty($member)) {
      $affected = $DevHzSql->deleteMember($id);

      if ($affected) {
        $this->addFlash('success', 'The member was deleted successfully.');
      } else {
        $this->addFlash('warning', 'The member was not deleted.');
      }
    }

    return $this->redirectToRoute('ps_dev_horizont_members');
  }
}
