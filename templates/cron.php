<?php

use models\parts\Disposals;
use models\Repairs;

Repairs::clearUnused();
Disposals::updateDisposalNum();
array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/uploads/temp/*'));
array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/uploads/temp/reports/*'));
