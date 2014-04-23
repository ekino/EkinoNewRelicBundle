<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Block;

use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewRelicTabsBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'reference' => false,
            'tabs'      => array(),
            'title'     => 'NewRelic Metrics',
            'height'    => '250px',
            'width'     => '100%',
            'template'  => 'EkinoNewRelicBundle:Block:block_core_new_relic_tabs.html.twig',
          ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $tabs = $blockContext->getSetting('tabs');

        foreach ($tabs as $pos => $tab) {
            $tabs[$pos] = array_merge(array(
                'title'     => 'NR Metric',
                'reference' => false
            ), $tab);
        }

        return $this->renderResponse('EkinoNewRelicBundle:Block:tabs.html.twig', array(
            'tabs'      => $tabs,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'context'   => $blockContext
        ));
    }
}
