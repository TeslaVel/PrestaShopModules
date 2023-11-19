<?php

declare(strict_types=1);

namespace PrestaShop\Module\ExtraAddress\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\Province;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Country;
use Zone;

class DevProvincesController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminProvinces';

    private $Province;

    public function __construct()
    {
        $this->Province = new Province;
    }

    public function indexAction(Request $request)
    {
        $token = $request->query->get('_token');

        $provinces = $this->Province->getAllProvinces(Province::COUNTRY_ID);

        return $this->render(
            '@Modules/extraaddress/views/templates/admin/provinceslist.html.twig',
            [
                'token' => $token,
                'ctr_content' => '',
                'provinces' => $provinces
            ]
        );
    }

    public function editAction(Request $request, int $id = null)
    {
        $token = $request->query->get('_token');
        $ctr_title = 'Entry Registration Form';
        $action_url = $this->generateUrl('ps_extra_address_provinces_save', ['_token' => $token]);
        $ctr_submit_action = 'Create';

        if ($id) {
            $ctr_title = 'Edit Province ';
            $ctr_submit_action = 'Update';
            $action_url = $this->generateUrl('ps_extra_address_provinces_save', ['id' => $id,'_token' => $token]);
        }

        $form = $this->buildForm(['id_country', 'id_zone', 'name'], true, $action_url);

        if ($id) {
            $province = $this->Province->getProvinceById($id);
            $form->setData($province);
        }

        return $this->render(
            '@Modules/extraaddress/views/templates/admin/provinceform.html.twig',
            [
                'ctr_title' => $ctr_title,
                'ctr_submit_action' => $ctr_submit_action,
                'form' => $form->createView()
            ]
        );
    }

    protected function buildForm($fields, $is_post = false , $action_url = null) {
        $filtered = $this->Province->getFilteredFields($fields);
        $id_country = $this->getContext()->country->id;
        $id_zone = $this->getContext()->country->id_zone;
        $id_lang = $this->getContext()->language->id;
        $countries = Country::getCountries($id_lang, true);
        $countriesOptions = $this->Province->EaUtils->optionIze('id_country', 'name', $countries);
        $zones = Zone::getZones();
        $zoneOptions = $this->Province->EaUtils->optionIze('id_zone', 'name', $zones);
        

        $form = $this->createFormBuilder();
        foreach ($filtered as $key => $value) {
            if ($key == 'id_country') {
                $form->add(
                    $key,
                    ChoiceType::class,
                    [
                        'label' => 'Zona',
                        'required' => true,
                        'choices' => $countriesOptions,
                        'data' => $id_country
                    ]
                );
            } elseif ($key == 'id_zone') {
               $form->add(
                    $key,
                    ChoiceType::class,
                    [
                        'label' => 'Pais',
                        'required' => true,
                        'choices' => $zoneOptions,
                        'data' => $id_zone
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

    public function saveAction(Request $request, int $id = null, )
    {
        // $id_lang = $this->getContext()->language->id;
        // $countries = Country::getCountries($id_lang, true);
        // $countriesOptions = $this->Province->EaUtils->optionIze('id_country', 'name', $countries);

        // $form = $this->createFormBuilder()
        //     ->add('id_country', ChoiceType::class, ['choices' => $countriesOptions, 'required' => true])
        //     ->add('name', TextType::class)
        //     ->getForm();
        $form = $this->buildForm($request->request->get('form'));
        $form->handleRequest($request);

        $flash_type = 'error';
        $flash_object = $this->trans('There are missing values', 'Admin.Notifications.Error');
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash($flash_type, $flash_object);

            return $this->redirectToRoute("ps_extra_address_provinces_edit");
        }

        $toProcess = $form->getData();
        $flash_type = 'success';

        try {
            if (filter_var($id, FILTER_VALIDATE_INT)) {
                $this->Province->updateData($toProcess, $id);

                $flash_object = $this->trans('Province was updated', 'Admin.Notifications.Success');
            } else {
                $last_id = $this->Province->insertData($toProcess);

                $flash_object = $this->trans('Province was created', 'Admin.Notifications.Success');

                if (!filter_var($last_id, FILTER_VALIDATE_INT)) {
                    $flash_type = 'error';

                    $flash_object = $this->trans('Province could not be created', 'Admin.Notifications.Error');
                }
            }
        } catch (SupplierException $e) {
            $flash_object = $this->getErrorMessageForException($e, $this->getErrorMessages());

            return $this->redirectToRoute("ps_extra_address_provinces_edit");
        }

        $this->addFlash($flash_type, $flash_object);

        return $this->redirectToRoute("ps_extra_address_provinces");
    }

    public function deleteAction(Request $request, $id)
    {
        $entry = $this->Province->getEntryById($id);

        if (!empty($entry)) {
            $affected = $this->Province->deleteProvince($id);

            if ($affected) {
                $this->addFlash('success', 'The entry was deleted successfully.');
            } else {
                $this->addFlash('warning', 'The entry was not deleted.');
            }
        }

        return $this->redirectToRoute('ps_extra_address_provinces');
    }
}
