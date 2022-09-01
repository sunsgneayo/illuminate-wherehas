<?php

namespace Sunsgne\WhereHas\Builder;

/**
 * @purpose where has not in
 * @date 2022/9/1
 * @author sunsgne
 */
class WhereHasNotIn extends WhereHasIn
{

    /**
     * @var string
     */
    protected string $method = 'whereNotIn';
}