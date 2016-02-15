<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @copyright 2010 onwards James McQuillan (http://pdyn.net)
 * @author James McQuillan <james@pdyn.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace pdyn\autoloader;

/**
 * A class autoloader.
 */
class Autoloader {
	/** @var array An array of base namespaces and their locations. Key is namespace, value is absolute path.*/
	protected $namespaces;

	/**
	 * Constructor.
	 *
	 * @param array $namespaces An array of base namespaces and their locations. Key is namespace, value is absolute path.
	 */
	public function __construct($namespaces) {
		$this->namespaces = $namespaces;
	}

	/**
	 * Add a new base namespace.
	 *
	 * @param string $namespace The root namespace name.
	 * @param string $path The absolute path to the root of the namespace.
	 */
	public function addnamespace($namespace, $path) {
		$this->namespaces[$namespace] = $path;
	}

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register() {
		spl_autoload_register([$this, 'loadclass']);
	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister() {
		spl_autoload_unregister([$this, 'loadclass']);
	}

	/**
	 * Translate a fully-qualified class name into an absolute path to the file that contains that class.
	 *
	 * @param string $classname The fully-qualified class name.
	 * @return string The absolute path to the file.
	 */
	public function classtofile($classname) {
		$classname = trim($classname, '\\');
		foreach ($this->namespaces as $namespace => $path) {
			if (mb_strpos($classname, $namespace) === 0) {
				$file = $path.DIRECTORY_SEPARATOR.mb_substr($classname, mb_strlen($namespace) + 1);
				$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
				return $file.'.php';
			}
		}
		return false;
	}

	/**
	 * Translate a fully-qualified class name into an absolute path to the file that contains that class.
	 *
	 * @param string $classname The fully-qualified class name.
	 * @return string The absolute path to the file.
	 */
	public function classtofile_old($classname) {
		$classname = trim($classname, '\\');
		$classmainns = mb_substr($classname, 0, mb_strpos($classname, '\\', 1));
		if (isset($this->namespaces[$classmainns])) {
			$includepath = $this->namespaces[$classmainns];
			$filename = '';
			if (false !== ($lastnspos = mb_strripos($classname, '\\'))) {
				$namespace = mb_substr($classname, 0, $lastnspos);
				$classname = mb_substr($classname, $lastnspos + 1);
				$filename = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
			}
			$filename .= str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php';
			return $includepath.DIRECTORY_SEPARATOR.$filename;
		} else {
			return false;
		}
	}

	/**
	 * Determine if a file exists for a given class name.
	 *
	 * @param string $classname A fully-qualified class name.
	 * @return bool Whether a file for this class exists or not.
	 */
	public function file_exists($classname) {
		$file = $this->classtofile($classname);
		if (!empty($file)) {
			return file_exists($file);
		} else {
			return false;
		}
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $classname The name of the class to load.
	 */
	public function loadclass($classname) {
		$file = $this->classtofile($classname);
		if (!empty($file) && file_exists($file)) {
			include_once($file);
		}
	}
}
