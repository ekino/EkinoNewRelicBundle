<?php

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
            'url'      => false,
            'height'   => 300,
            'width'    => 500,
            'template' => 'EkinoNewRelicBundle:Block:block_core_new_relic.html.twig',
          ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $content = $this->getTemplating()->render('EkinoNewRelicBundle:Block:block_core_new_relic.html.twig', array(
            'url'    => $blockContext->getSetting('url'),
            'width'  => $blockContext->getSetting('width'),
            'height' => $blockContext->getSetting('height'),
        ));

        return new Response($content);
    }
} 