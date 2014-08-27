<?php

namespace TestWork\PhpSpecExtension;

use Exception;
use Behat\Testwork\Exception\Stringer;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Formatter\Presenter\Differ\StringEngine;
use PhpSpec\Formatter\Presenter\Differ\ArrayEngine;
use PhpSpec\Formatter\Presenter\Differ\ObjectEngine;
use PhpSpec\Formatter\Presenter\TaggedPresenter;
use PhpSpec\Formatter\Presenter\StringPresenter;
use PhpSpec\Formatter\Presenter\Differ\Differ;
use SebastianBergmann\Exporter\Exporter;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Strings PhpSpec assertion exceptions.
 *
 * @see ExceptionPresenter
 *
 * @author Florian Klein <florian.klein@free.fr>
 */
final class ExceptionStringer implements Stringer\ExceptionStringer
{
    public function __construct(PresenterInterface $presenter = null)
    {
        $this->presenter = $presenter ?: new TaggedPresenter(new Differ([
            new StringEngine,
            new ArrayEngine,
            new ObjectEngine(new Exporter, new StringEngine),
        ]));
    }
    /**
     * {@inheritdoc}
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof \Phpspec\Exception\Exception;
    }

    /**
     * {@inheritdoc}
     */
    public function stringException(Exception $exception, $verbosity)
    {
        return $this->presenter->presentException($exception, $verbosity > OutputPrinter::VERBOSITY_NORMAL);
    }
}
