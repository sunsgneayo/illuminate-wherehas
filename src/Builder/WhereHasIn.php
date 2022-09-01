<?php

namespace Sunsgne\WhereHas\Builder;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

/**
 * @purpose
 * @date 2022/9/1
 * @author sunsgne
 */
class WhereHasIn
{


    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var string
     */
    protected string $relation;

    /**
     * @var string
     */
    protected string $nextRelation;

    /**
     * @var \Closure
     */
    protected \Closure $callback;

    /**
     * @var string
     */
    protected string $method = 'whereIn';

    public function __construct(Builder $builder, $relation, $callback)
    {
        $this->builder = $builder;
        $this->relation = $relation;
        $this->callback = $callback;
    }

    /**
     * @return Builder
     *
     * @throws \Exception
     */
    public function execute(): Builder
    {
        if (! $this->relation) {
            return $this->builder;
        }

        return $this->where(
            $this->formatRelation()
        );
    }

    /**
     * @param Relation $relation
     *
     * @return Builder
     *
     * @throws \Exception
     */
    protected function where(Relation $relation): Builder
    {
        if ($relation instanceof Relations\MorphTo) {
            throw new \Exception('Please use whereHasMorphIn() for MorphTo relationships.');
        }

        $relationQuery = $this->getRelationQuery($relation);
        $method = $this->method;

        if (
            $relation instanceof Relations\MorphOne
            || $relation instanceof Relations\MorphMany
        ) {
            return $this->builder->{$method}(
                $relation->getQualifiedParentKeyName(),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedForeignKeyName())
                        ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignKeyName())
                        ->where($relation->getQualifiedMorphType(), $relation->getMorphClass())
                )
            );
        }

        if ($relation instanceof Relations\MorphToMany) {
            return $this->builder->{$method}(
                $relation->getQualifiedParentKeyName(),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedForeignPivotKeyName())
                        ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignPivotKeyName())
                        ->where($relation->getTable().'.'.$relation->getMorphType(), $relation->getMorphClass())
                )
            );
        }

        // BelongsTo
        if ($relation instanceof Relations\BelongsTo) {
            return $this->builder->{$method}(
                $this->getRelationQualifiedForeignKeyName($relation),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedOwnerKeyName())
                        ->whereColumn($this->getRelationQualifiedForeignKeyName($relation), $relation->getQualifiedOwnerKeyName())
                )
            );
        }

        if (
            $relation instanceof Relations\HasOne
            || $relation instanceof Relations\HasMany
        ) {
            return $this->builder->{$method}(
                $relation->getQualifiedParentKeyName(),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedForeignKeyName())
                        ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignKeyName())
                )
            );
        }

        // BelongsToMany
        if ($relation instanceof Relations\BelongsToMany) {
            return $this->builder->{$method}(
                $relation->getQualifiedParentKeyName(),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedForeignPivotKeyName())
                        ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignPivotKeyName())
                )
            );
        }

        if (
            $relation instanceof Relations\HasOneThrough
            || $relation instanceof Relations\HasManyThrough
        ) {
            return $this->builder->{$method}(
                $relation->getQualifiedLocalKeyName(),
                $this->withRelationQueryCallback(
                    $relationQuery
                        ->select($relation->getQualifiedFirstKeyName())
                        ->whereColumn($relation->getQualifiedLocalKeyName(), $relation->getQualifiedFirstKeyName())
                )
            );
        }

        throw new \Exception(sprintf('%s does not support "whereHasIn".', get_class($relation)));
    }

    /**
     * @param Relation $relation
     *
     * @return Builder
     */
    protected function getRelationQuery(Relation $relation): Builder
    {
        $q = $relation->getQuery();

        if ($this->builder->getModel()->getConnectionName() !== $q->getModel()->getConnectionName()) {
            $databaseName = $this->getRelationDatabaseName($q);
            $table = $q->getModel()->getTable();

            if (! Str::contains($table, ["`$databaseName`.", "{$databaseName}."])) {
                $q->from("{$databaseName}.{$table}");
            }
        }

        return $q;
    }

    protected function getRelationDatabaseName($q)
    {
        return config('database.connections.'.$q->getModel()->getConnectionName().'.database');
    }

    protected function getRelationQualifiedForeignKeyName($relation)
    {
        if (method_exists($relation, 'getQualifiedForeignKeyName')) {
            return $relation->getQualifiedForeignKeyName();
        }

        return $relation->getQualifiedForeignKey();
    }

    /**
     * @return Relation
     */
    protected function formatRelation()
    {
        if (is_object($this->relation)) {
            $relation = $this->relation;
        } else {
            $relationNames = explode('.', $this->relation);
            $this->nextRelation = implode('.', array_slice($relationNames, 1));

            $method = $relationNames[0];

            $relation = Relation::noConstraints(function () use ($method) {
                return $this->builder->getModel()->$method();
            });
        }

        return $relation;
    }

    /**
     * @param Builder $relation
     *
     * @return Builder
     */
    protected function withRelationQueryCallback($relationQuery): Builder
    {
        return call_user_func($this->callback, $this->nextRelation, $relationQuery);
    }

}