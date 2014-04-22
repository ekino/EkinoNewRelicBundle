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
class NewRelicBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'reference' => false,
            'height'    => '300px',
            'width'     => '100%',
            'template'  => 'EkinoNewRelicBundle:Block:block_core_new_relic.html.twig',
          ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $reference = $blockContext->getSetting('reference');

        if ("https" != substr($reference, 0, 5)) {
            $reference = sprintf('https://rpm.newrelic.com/public/charts/%s', $reference);
        }

        $content = $this->getTemplating()->render('EkinoNewRelicBundle:Block:block_core_new_relic.html.twig', array(
            'reference' => $reference,
            'width'     => $blockContext->getSetting('width'),
            'height'    => $blockContext->getSetting('height'),
        ));

        return new Response($content);
    }
}
