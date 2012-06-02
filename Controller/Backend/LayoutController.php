<?php

/*
 * This file is part of the Roger CMS Bundle
 *
 * (c) Theodo <contact@theodo.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Theodo\RogerCmsBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Theodo\RogerCmsBundle\Form\LayoutType;

/**
 * Controller for backend layout section
 *
 * @author Mathieu Dähne <mathieud@theodo.fr>
 * @author Cyrille Jouineau <cyrillej@theodo.fr>
 * @author Marek Kalnik <marekk@theodo.fr>
 * @author Fabrice Bernhard <fabriceb@theodo.fr>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class LayoutController extends Controller
{
    /**
     * Layouts list
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function indexAction()
    {
        $layouts = $this->get('roger.content_repository')->findAll('layout');

        return $this->render('TheodoRogerCmsBundle:Layout:index.html.twig', array(
            'layouts' => $layouts
        ));
    }

    /**
     * Edit a layout
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @author Mathieu Dähne <mathieud@theodo.fr>
     * @since 2011-06-20
     * @since 2011-06-29 cyrillej ($hasErrors, copied from PageController by vincentg)
     * @since 2011-07-06 mathieud ($hasErrors deleted)
     */
    public function editAction($id)
    {
        $layout = null;
        if ($id) {
            $layout = $this->get('roger.content_repository')->findOneById($id, 'layout');
        }

        $form = $this->createForm(new LayoutType(), $layout);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // remove twig cached file
                if ($layout) {
                    $this->get('roger.caching')->invalidate('layout:'.$layout->getName());
                }

                // save layout
                $layout = $form->getData();
                $this->get('roger.content_repository')->save($layout);

                $this->get('roger.caching')->warmup('layout:'.$layout->getName());

                // Set redirect route
                $redirect = $this->redirect($this->generateUrl('layout_list'));
                if ($request->get('save-and-edit')) {
                    $redirect = $this->redirect($this->generateUrl('layout_edit', array('id' => $layout->getId())));
                }

                return $redirect;
            }
        }

        return $this->render('TheodoRogerCmsBundle:Layout:edit.html.twig', array(
            'layout' => $layout,
            'form' => $form->createView(),
        ));
    }

    /**
     * Layout remove
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @author Mathieu Dähne <mathieud@theodo.fr>
     * @since 2011-06-21
     */
    public function removeAction($id)
    {
        $layout = $this->get('roger.content_repository')->findOneById($id, 'layout');

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $this->get('roger.content_repository')->remove($layout);

            return $this->redirect($this->generateUrl('layout_list'));
        }

        return $this->render('TheodoRogerCmsBundle:Layout:remove.html.twig', array(
            'layout' => $layout
        ));
    }
}
