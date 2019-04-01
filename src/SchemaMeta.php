<?php
declare(strict_types = 1);

namespace MyaZaki\LaravelSchemaspyMeta;

class SchemaMeta
{
    const XML_TEMPLATE = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<schemaMeta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://schemaspy.org/xsd/6/schemameta.xsd" >
    <tables />
</schemaMeta>
XML;

    public static function generate($files, $namespace, $xml_path)
    {
        $relationship_list = [];
        foreach ($files as $target_file) {
            $parsed_ast = \ast\parse_file($target_file, 60);

            $relationships = new Relationships($parsed_ast, $namespace);
            $relationship_list = array_merge($relationship_list, $relationships->get());
        }

        if (is_writable($xml_path)) {
            $sxe = new \SimpleXMLElement($xml_path, 0, true);
        } else {
            $sxe = new \SimpleXMLElement(self::XML_TEMPLATE);
        }

        foreach ($relationship_list as $relationship) {
            self::addRelationshipNodeToXml($sxe->tables, $relationship);
        }

        return file_put_contents($xml_path, trim($sxe->asXml()));
    }

    protected static function addRelationshipNodeToXml(\SimpleXMLElement $sxe, Relationship $relationship)
    {
        $getTable = function ($sxe) use ($relationship) {
            return $sxe->xpath("table[@name=\"{$relationship->related_table}\"]")[0] ?? null;
        };

        $getColumn = function ($sxe) use ($relationship) {
            return $sxe->xpath("column[@name=\"{$relationship->foreign_key}\"]")[0] ?? null;
        };

        $getForeignKey = function ($sxe) use ($relationship) {
            return $sxe->xpath("foreignKey[@table=\"{$relationship->parent_table}\"][@column=\"{$relationship->local_key}\"]")[0] ?? null;
        };

        if (is_null($getTable($sxe))) {
            $sxe->addChild('table')
                ->addAttribute('name', $relationship->related_table);
        }

        if (is_null($getColumn($getTable($sxe)))) {
            $getTable($sxe)->addChild('column')
                ->addAttribute('name', $relationship->foreign_key);
        }

        if (is_null($getForeignKey($getColumn($getTable($sxe))))) {
            $node = $getColumn($getTable($sxe))->addChild('foreignKey');
            $node->addAttribute('table', $relationship->parent_table);
            $node->addAttribute('column', $relationship->local_key);
        }
    }
}
