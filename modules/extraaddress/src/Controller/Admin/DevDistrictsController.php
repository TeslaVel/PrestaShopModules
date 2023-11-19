<?php

declare(strict_types=1);

namespace PrestaShop\Module\ExtraAddress\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\District;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\Province;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Country;

class DevDistrictsController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDistricts';
 
    private $District;
    private $Province;

    public function __construct()
    {
        $this->District = new District;
        $this->Province = new Province;
        
    }

    public function indexAction(Request $request)
    {
        $token = $request->query->get('_token');
        $id_country = empty($request->request->get('form')) ? $this->getContext()->country->id : $request->request->get('form')['id_country'];
        $id_lang = $this->getContext()->language->id;
        $districts = $this->District->getAllDistricts($id_country);
        $countries = Country::getCountries($id_lang, true);

        $action_url = $this->generateUrl('ps_extra_address_districts', ['_token' => $token]);
        $form = $this->buildForm(['id_country'], true, $action_url, (int)$id_country);

        return $this->render(
            '@Modules/extraaddress/views/templates/admin/districtslist.html.twig',
            [
                'token' => $token,
                'ctr_content' => '',
                'districts' => $districts,
                'countries' => $countries,
                'formSearch' => $form->createView()
            ]
        );
    }

    public function editAction(Request $request, int $id = null)
    {
        $token = $request->query->get('_token');
        $ctr_title = 'Entry Registration Form';
        $action_url = $this->generateUrl('ps_extra_address_districts_save', ['_token' => $token]);
        $ctr_submit_action = 'Create';

        if ($id) {
            $ctr_title = 'Edit District ';
            $ctr_submit_action = 'Update';
            $action_url = $this->generateUrl('ps_extra_address_districts_save', ['id' => $id, '_token' => $token]);
        }

        $form = $this->buildForm(['id_country', 'id_state', 'name'], true, $action_url);

        if ($id) {
            $district = $this->District->getDistrictById($id);
            $form->setData($district);
        }

        return $this->render(
            '@Modules/extraaddress/views/templates/admin/districtform.html.twig',
            [
                'ctr_title' => $ctr_title,
                'ctr_submit_action' => $ctr_submit_action,
                'form' => $form->createView()
            ]
        );
    }

    protected function buildForm($fields, $is_post = false , $action_url = null, $id_country = null) {
        $id_country = (int) $id_country | $this->getContext()->country->id;
        $filtered = $this->District->getFilteredFields($fields);
 
        $states = $this->Province->getAllProvinces(Province::COUNTRY_ID);
        $stateOptions = $this->District->EaUtils->optionIze('id_state', 'name', $states);
        $id_lang = $this->getContext()->language->id;
        $countries = Country::getCountries($id_lang, true);
        $countriesOptions = $this->District->EaUtils->optionIze('id_country', 'name', $countries);
        
        
        $form = $this->createFormBuilder();
        foreach ($filtered as $key => $value) {
            if ($key == 'id_state') {
                $form->add(
                    $key,
                    ChoiceType::class,
                    [
                        'label' => 'Estado',
                        'required' => true,
                        'choices' => $stateOptions,
                    ]
                );
            } elseif ($key == 'id_country') {
               $form->add(
                    $key,
                    ChoiceType::class,
                    [
                        'label' => 'Pais',
                        'required' => true,
                        'choices' => $countriesOptions,
                        'data' => $id_country
                    ]
                );
            } else {
              $form->add($key, TextType::class, ['required' => true]);
            }
        }

        if ($is_post) {
            $form->setMethod('POST');
        }

        if ($action_url) {
            $form->setAction($action_url);
        }

        return $form->getForm();
    }

    public function saveAction(Request $request, int $id = null)
    {
        $form = $this->buildForm($request->request->get('form'));
        $form->handleRequest($request);


        $flash_type = 'error';
        $flash_object = $this->trans('There are missing values', 'Admin.Notifications.Error');
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash($flash_type, $flash_object);

            return $this->redirectToRoute("ps_extra_address_districts_edit");
        }

        $toProcess = $form->getData();
        $flash_type = 'success';

        try {
            if (filter_var($id, FILTER_VALIDATE_INT)) {
                $this->District->updateData($toProcess, $id);

                $flash_object = $this->trans('District was updated', 'Admin.Notifications.Success');
            } else {
                $last_id = $this->District->insertData($toProcess);

                $flash_object = $this->trans('District was created', 'Admin.Notifications.Success');

                if (!filter_var($last_id, FILTER_VALIDATE_INT)) {
                    $flash_type = 'error';

                    $flash_object = $this->trans('District could not be created', 'Admin.Notifications.Error');
                }
            }
        } catch (SupplierException $e) {
            $flash_object = $this->getErrorMessageForException($e, $this->getErrorMessages());

            return $this->redirectToRoute("ps_extra_address_districts_edit");
        }

        $this->addFlash($flash_type, $flash_object);

        return $this->redirectToRoute("ps_extra_address_districts");
    }

    public function deleteAction(Request $request, $id)
    {
        $entry = $this->District->getEntryById($id);

        if (!empty($entry)) {
            $affected = $this->District->deleteDistrict($id);

            if ($affected) {
                $this->addFlash('success', 'The entry was deleted successfully.');
            } else {
                $this->addFlash('warning', 'The entry was not deleted.');
            }
        }

        return $this->redirectToRoute('ps_extra_address_districts');
    }
}
