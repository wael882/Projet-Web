<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* base.html.twig */
class __TwigTemplate_5b8a140bee734a61eb4845c4e35c14f0 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'styles' => [$this, 'block_styles'],
            'description' => [$this, 'block_description'],
            'header' => [$this, 'block_header'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!doctype html>
<html lang=\"fr\">
  <head>
    <meta charset=\"UTF-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
    <title>";
        // line 6
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        yield "</title>
    <link rel=\"stylesheet\" href=";
        // line 7
        yield from $this->unwrap()->yieldBlock('styles', $context, $blocks);
        yield "/>
    <meta name=\"description\" ";
        // line 8
        yield from $this->unwrap()->yieldBlock('description', $context, $blocks);
        yield " />
  </head>
  <body>

    ";
        // line 12
        yield from $this->unwrap()->yieldBlock('header', $context, $blocks);
        // line 15
        yield "
    <main>
      ";
        // line 17
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 18
        yield "    </main>

    ";
        // line 20
        yield from $this->load("partials/footer.html.twig", 20)->unwrap()->yield($context);
        // line 21
        yield "
  </body>
</html>
";
        yield from [];
    }

    // line 6
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Stagio";
        yield from [];
    }

    // line 7
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_styles(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "  ";
        yield from [];
    }

    // line 8
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_description(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield " content=\"Stagio\"";
        yield from [];
    }

    // line 12
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_header(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 13
        yield "      ";
        yield from $this->load("partials/header.html.twig", 13)->unwrap()->yield($context);
        // line 14
        yield "    ";
        yield from [];
    }

    // line 17
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "base.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  139 => 17,  134 => 14,  131 => 13,  124 => 12,  113 => 8,  102 => 7,  91 => 6,  83 => 21,  81 => 20,  77 => 18,  75 => 17,  71 => 15,  69 => 12,  62 => 8,  58 => 7,  54 => 6,  47 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "base.html.twig", "/mnt/c/projet ecole/Projet-Web-main/templates/base.html.twig");
    }
}
