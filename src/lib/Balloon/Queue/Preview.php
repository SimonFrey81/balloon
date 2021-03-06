<?php
declare(strict_types=1);

/**
 * Balloon
 *
 * @author      Raffael Sahli <sahli@gyselroth.net>
 * @copyright   Copryright (c) 2012-2017 gyselroth GmbH (https://gyselroth.com)
 * @license     GPLv3 https://opensource.org/licenses/GPL-3.0
 */

namespace Balloon\Queue;

use \Psr\Log\LoggerInterface as Logger;
use Balloon\Config;
use Balloon\Filesystem;
use \MongoDB\Database;
use Balloon\Preview as PreviewCreator;

class Preview extends AbstractJob
{
    /**
     * Run job
     *
     * @return bool
     */
    public function run(Filesystem $fs, Logger $logger, Config $config): bool
    {
        $file = $fs->findNodeWithId($this->data['id']);

        $logger->info("create preview for [".$this->data['id']."]", [
            'category' => get_class($this),
        ]);

        $preview = new PreviewCreator($logger, $config->plugins->preview->config);
        $content = $preview->create($file);
        $file->setPreview($content);
        return true;
    }
}
