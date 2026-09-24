<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use InvalidArgumentException;

/**
 * A file the package refuses to read. The message is safe to show the user.
 */
final class UnsupportedSpreadsheet extends InvalidArgumentException {}
