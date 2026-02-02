<?php

namespace Phpactor\Extension\LanguageServerHover;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\LanguageServerHover\Twig\TwigFunctions;
use Phpactor\Extension\ObjectRenderer\ObjectRendererExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\LanguageServerHover\Handler\HoverHandler;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class LanguageServerHoverExtension implements Extension
{
    // TODO: file type based hover?
    const PARAM_ENABLE = 'language_server_hover.enable';

    public function load(ContainerBuilder $container): void
    {
        $container->register('language_server_completion.handler.hover', function (Container $container) {
            if ($container->parameter(self::PARAM_ENABLE)->bool() === false) {
                return null;
            }

            return new HoverHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(ObjectRendererExtension::SERVICE_MARKDOWN_RENDERER)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);

        $container->register(TwigFunctions::class, function (Container $container) {
            return new TwigFunctions();
        }, [
            ObjectRendererExtension::TAG_TWIG_EXTENSION => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLE => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLE => 'Enable language server hover',
        ]);
    }
}
