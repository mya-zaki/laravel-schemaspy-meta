<?php
declare(strict_types = 1);

namespace MyaZaki\LaravelSchemaspyMeta;

class Relationships
{
    private static $relationshipTypes = [
        'hasOne',
        'hasMany',
        'belongsTo',
        'belongsToMany',
    ];

    private $modelNamespace;
    private $parentClass;
    private $relationships = [];

    public function __construct(\ast\Node $ast, $modelNamespace)
    {
        $this->modelNamespace = $modelNamespace;

        $ast_class = $this->getAstClass($ast);
        
        $this->parentClass = $this->modelNamespace . '\\' . $ast_class->children['name'];

        $ast_methods = $this->getAstMethods($ast_class);

        foreach ($ast_methods as $ast_method) {
            if ($this->isCallingRelationship($ast_method)) {
                $this->makeRelationship($ast_method);
            }
        }
    }

    public function get() : array
    {
        return $this->relationships;
    }

    private function getAstClass(\ast\Node $ast): \ast\Node
    {
        foreach ($ast->children as $child) {
            if ($child instanceof \ast\Node && $child->kind === \ast\AST_CLASS) {
                return $child;
            }
        }
    }

    private function getAstStmtList(\ast\Node $ast): \ast\Node
    {
        foreach ($ast->children as $child) {
            if ($child instanceof \ast\Node && $child === \ast\AST_STMT_LIST) {
                return $child;
            }
        }
    }

    private function getAstMethods(\ast\Node $ast): array
    {
        $methods = [];
        foreach ($ast->children as $child) {
            if ($child instanceof \ast\Node && $child->kind === \ast\AST_STMT_LIST) {
                return $this->getAstMethods($child);
            }
            if ($child instanceof \ast\Node && $child->kind === \ast\AST_METHOD) {
                $methods[] = $child;
            }
        }
        return $methods;
    }

    private function isCallingRelationship(\ast\Node $ast_method): bool
    {
        return !is_null($this->getRelationshipType($ast_method));
    }

    private function getRelationshipType(\ast\Node $ast_method): ?string
    {
        foreach ($ast_method->children as $child) {
            if ($child instanceof \ast\Node) {
                if ($child->kind === \ast\AST_STMT_LIST) {
                    return $this->getRelationshipType($child);
                }
                
                foreach ($child->children as $grandchild) {
                    if ($grandchild instanceof \ast\Node && $grandchild->kind === \ast\AST_METHOD_CALL) {
                        if (isset($grandchild->children['method'])) {
                            $type = $grandchild->children['method'];
                            if (in_array($type, self::$relationshipTypes)) {
                                return $type;
                            }
                        }
                        foreach ($grandchild->children as $prop => $great_grandchild) {
                            if ($child instanceof \ast\Node) {
                                return $this->getRelationshipType($child);
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
    
    private function makeRelationship(\ast\Node $ast)
    {
        $parent_class = $this->parentClass;
        $parent_model = new $parent_class();
        $method_name = $ast->children['name'];

        $relation = $parent_model->$method_name();
        $related_table = $relation->getRelated()->getTable();
        $parent_table = $parent_model->getTable();

        $relationship_type = $this->getRelationshipType($ast);
        switch ($relationship_type) {
            case 'hasOne':
            case 'hasMany':
                $foreign_key = $relation->getForeignKeyName();
                $arr = explode('.', $relation->getQualifiedParentKeyName());
                $local_key = end($arr);
                $this->relationships[] = new Relationship($related_table, $foreign_key, $parent_table, $local_key);
                break;
            case 'belongsTo':
                $foreign_key = method_exists($relation, 'getForeignKey') ? $relation->getForeignKey() : $relation->getForeignKeyName();
                $local_key = method_exists($relation, 'getOwnerKey') ? $relation->getOwnerKey() : $relation->getOwnerKeyName();
                $this->relationships[] = new Relationship($parent_table, $foreign_key, $related_table, $local_key);
                break;
            case 'belongsToMany':
                $intermediate_table = $relation->getTable();
                $foreign_pivot_key = $relation->getForeignPivotKeyName();
                $related_pivot_key = $relation->getRelatedPivotKeyName();
                $arr = explode('.', $relation->getQualifiedParentKeyName());
                $parent_local_key = end($arr);

                $relation_prop_ref = (new \ReflectionClass($relation))->getProperty('relatedKey');
                $relation_prop_ref->setAccessible(true);
                $related_local_key = $relation_prop_ref->getValue($relation);
                $this->relationships[] = new Relationship($intermediate_table, $foreign_pivot_key, $parent_table, $parent_local_key);
                $this->relationships[] = new Relationship($intermediate_table, $related_pivot_key, $related_table, $related_local_key);
                break;
        }
    }
}
