<?php

namespace Doctrine\Website\Tests\Docs;

use Doctrine\Website\Docs\APIBuilder;
use Doctrine\Website\Projects\Project;
use Doctrine\Website\Projects\ProjectVersion;
use Doctrine\Website\ProcessFactory;
use PHPUnit\Framework\TestCase;

class APIBuilderTest extends TestCase
{
    /** @var ProcessFactory */
    private $processFactory;

    /** @var string */
    private $projectsPath;

    /** @var string */
    private $sculpinSourcePath;

    /** @var APIBuilder */
    private $apiBuilder;

    protected function setUp()
    {
        $this->processFactory = $this->createMock(ProcessFactory::class);
        $this->projectsPath = '/data/doctrine';
        $this->sculpinSourcePath = '/data/doctrine-website/source';

        $this->apiBuilder = $this->getMockBuilder(APIBuilder::class)
            ->setConstructorArgs([
                $this->processFactory,
                $this->projectsPath,
                $this->sculpinSourcePath
            ])
            ->setMethods(['filePutContents', 'unlinkFile'])
            ->getMock()
        ;
    }

    public function testBuildAPIDocs()
    {
        $project = new Project([
            'slug' => 'orm',
            'repositoryName' => 'doctrine2',
            'codePath' => '/src',
        ]);
        $version = new ProjectVersion([
            'slug' => '2.0',
        ]);

        $configContent = <<<CONFIG
<?php

return new Sami\Sami('/data/doctrine/doctrine2/src', [
    'build_dir' => '/data/doctrine-website/source/api/orm/2.0',
    'cache_dir' => '/data/doctrine/doctrine2/cache',
]);
CONFIG;

        $this->apiBuilder->expects($this->once())
            ->method('filePutContents')
            ->with('/data/doctrine/doctrine2/sami.php', $configContent);

        $this->processFactory->expects($this->once())
            ->method('run')
            ->with('php /data/doctrine-website/source/../sami.phar update /data/doctrine/doctrine2/sami.php --verbose');

        $this->apiBuilder->expects($this->once())
            ->method('unlinkFile')
            ->with('/data/doctrine/doctrine2/sami.php');

        $this->apiBuilder->buildAPIDocs($project, $version);
    }
}
