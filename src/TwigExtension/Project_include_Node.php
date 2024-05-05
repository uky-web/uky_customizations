<?php

namespace Drupal\ukd8_customizations\TwigExtension;

use Twig\Compiler;
use Twig\Node\IncludeNode;

class Project_include_Node extends IncludeNode {


    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        //Specific to uky_customizations, get name for comments
        $expr = $this->getNode('expr');
        $name = null;
        if ($expr->hasAttribute('value')) {
            $name = $expr->getAttribute('value');
        }
        if ($name) {
            $compiler->write("echo '<!-- TWIG INCLUDE : " . $name . "\" -->';\n");
        }

        if ($this->getAttribute('ignore_missing')) {
            $template = $compiler->getVarName();

            $compiler
                ->write(sprintf("$%s = null;\n", $template))
                ->write("try {\n")
                ->indent()
                ->write(sprintf('$%s = ', $template))
            ;

            $this->addGetTemplate($compiler);

            $compiler
                ->raw(";\n")
                ->outdent()
                ->write("} catch (LoaderError \$e) {\n")
                ->indent()
                ->write("// ignore missing template\n")
                ->outdent()
                ->write("}\n")
                ->write(sprintf("if ($%s) {\n", $template))
                ->indent()
                ->write(sprintf('yield from $%s->unwrap()->yield(', $template))
            ;

            $this->addTemplateArguments($compiler);
            $compiler
                ->raw(");\n")
                ->outdent()
                ->write("}\n")
            ;
        } else {
            $compiler->write('yield from ');
            $this->addGetTemplate($compiler);
            $compiler->raw('->unwrap()->yield(');
            $this->addTemplateArguments($compiler);
            $compiler->raw(");\n");
        }

        //specific to uky_customizations
        if ($name) {
            $compiler->write("echo '<!-- END TWIG INCLUDE : " . $name . "\" -->';\n");
        }
    }
}
