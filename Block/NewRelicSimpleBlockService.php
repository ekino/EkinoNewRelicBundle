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

/**
 * Class NewRelicBlockService
 *
 * @author Ilan Benichou <ibenichou@ekino.com>
 */
class NewRelicSimpleBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'reference' => false,
            'title'     => 'NewRelic Metrics',
            'height'    => '250px',
            'width'     => '100%',
            'template'  => 'EkinoNewRelicBundle:Block:block_core_new_relic.html.twig',
          ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse('EkinoNewRelicBundle:Block:simple.html.twig', array(
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
            'context'   => $blockContext
        ));
    }
}
