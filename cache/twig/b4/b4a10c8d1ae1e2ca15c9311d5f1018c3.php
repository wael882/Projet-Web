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

/* rechercher.twig */
class __TwigTemplate_bb1e5d8ae845b7edf81a050ea27a9dea extends Template
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

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'description' => [$this, 'block_description'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("base.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Stagio - Accueil";
        yield from [];
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_description(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "content=\"Stagio - Acceuil\"";
        yield from [];
    }

    // line 7
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 8
        yield "    
    ";
        // line 9
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["rechercher"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["recherche"]) {
            // line 10
            yield "    ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["recherche"], "titre", [], "any", false, false, false, 10), "html", null, true);
            yield "
    ";
            // line 11
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["recherche"], "description", [], "any", false, false, false, 11), "html", null, true);
            yield "
    ";
            // line 12
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["recherche"], "date_offre", [], "any", false, false, false, 12), "html", null, true);
            yield "
    ";
            // line 13
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["recherche"], "duree_stage", [], "any", false, false, false, 13), "html", null, true);
            yield "
    <a href=\"/offre?id=";
            // line 14
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["recherche"], "id_offre", [], "any", false, false, false, 14), "html", null, true);
            yield "\">Voir l'offre →</a>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['recherche'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 16
        yield "
    ";
        // line 17
        if ((($context["page"] ?? null) > 1)) {
            // line 18
            yield "    <a href=\"/rechercher?page=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["page"] ?? null) - 1), "html", null, true);
            yield "\">← Précédent</a>
    ";
        }
        // line 20
        yield "    ";
        if ((($context["page"] ?? null) < ($context["totalPages"] ?? null))) {
            // line 21
            yield "    <a href=\"/rechercher?page=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["page"] ?? null) + 1), "html", null, true);
            yield "\">Suivant →</a>
    ";
        }
        // line 23
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "rechercher.twig";
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
        return array (  134 => 23,  128 => 21,  125 => 20,  119 => 18,  117 => 17,  114 => 16,  106 => 14,  102 => 13,  98 => 12,  94 => 11,  89 => 10,  85 => 9,  82 => 8,  75 => 7,  64 => 5,  53 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "rechercher.twig", "/mnt/c/projet ecole/Projet-Web-main/templates/rechercher.twig");
    }
}
