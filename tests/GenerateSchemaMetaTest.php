<?php
namespace MyaZaki\LaravelSchemaspyMeta\Tests;

use MyaZaki\LaravelSchemaspyMeta\SchemaMeta;

use Illuminate\Database\Capsule\Manager as Capsule;

class SchemaMetaTest extends \PHPUnit\Framework\TestCase
{
    protected $xml_path;

    public static function setUpBeforeClass(): void
    {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => 'database/database.sqlite',
            'prefix' => '',
        ]);
        $capsule->bootEloquent();
    }

    protected function setUp(): void
    {
        $this->xml_path = __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . 'meta.xml';
        @unlink($this->xml_path);
    }

    /**
     * Test the generate of a class
     */
    public function testGenerate()
    {
        $namespace = 'MyaZaki\\LaravelSchemaspyMeta\\Tests\\Models';

        $base_path = __DIR__ . DIRECTORY_SEPARATOR;
        $target_files = [
            $base_path . 'Models/User.php',
            $base_path . 'Models/Phone.php',
            $base_path . 'Models/Post.php',
            $base_path . 'Models/Comment.php',
        ];

        $expect = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<schemaMeta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://schemaspy.org/xsd/6/schemameta.xsd">
    <tables><table name="my_phones"><column name="user_id"><foreignKey table="users" column="id"/></column></table><table name="role_user"><column name="user_id"><foreignKey table="users" column="id"/></column><column name="role_id"><foreignKey table="roles" column="id"/></column></table><table name="comments"><column name="foreign_key"><foreignKey table="posts" column="other_key"/></column><column name="post_id"><foreignKey table="posts" column="id"/></column></table></tables>
</schemaMeta>
XML;
        SchemaMeta::generate($target_files, $namespace, $this->xml_path);
        $this->assertSame($expect, file_get_contents($this->xml_path));


        $target_files = [
            $base_path . 'Models/Role.php',
            $base_path . 'Models/UserRole.php',
            $base_path . 'Models/PasswordReset.php',
        ];

        $expect = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<schemaMeta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://schemaspy.org/xsd/6/schemameta.xsd">
    <tables><table name="my_phones"><column name="user_id"><foreignKey table="users" column="id"/></column></table><table name="role_user"><column name="user_id"><foreignKey table="users" column="id"/></column><column name="role_id"><foreignKey table="roles" column="id"/></column></table><table name="comments"><column name="foreign_key"><foreignKey table="posts" column="other_key"/></column><column name="post_id"><foreignKey table="posts" column="id"/></column></table><table name="permission_role"><column name="r_id"><foreignKey table="roles" column="id"/></column><column name="p_id"><foreignKey table="permissions" column="id"/></column></table><table name="password_resets"><column name="email"><foreignKey table="users" column="email"/></column></table></tables>
</schemaMeta>
XML;
        SchemaMeta::generate($target_files, $namespace, $this->xml_path);
        $this->assertSame($expect, file_get_contents($this->xml_path));
    }
}
