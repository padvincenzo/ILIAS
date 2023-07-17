<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MetaData\Structure\Dictionaries\Tags;

use ILIAS\MetaData\Paths\PathInterface;

class TagAssignment implements TagAssignmentInterface
{
    protected PathInterface $path;
    protected TagInterface $tag;

    public function __construct(
        PathInterface $path,
        TagInterface $tag
    ) {
        $this->path = $path;
        $this->tag = $tag;
    }

    public function matchesPath(PathInterface $path): bool
    {
        return $path->toString() === $this->path->toString();
    }

    public function tag(): TagInterface
    {
        return $this->tag;
    }
}
