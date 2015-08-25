<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Authors: Frank M. Kromann <frank@kromann.info>                       |
// |          David Coallier <davidc@php.net>                             |
// |          Lorenzo Alberton <l.alberton@quipo.it>                      |
// +----------------------------------------------------------------------+
//
// $Id: mssql.php,v 1.109 2008/03/05 12:55:57 afz Exp $
//

require_once 'MDB2/Driver/Manager/Common.php';

// {{{ class MDB2_Driver_Manager_mssql

/**
 * MDB2 MSSQL driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author  Frank M. Kromann <frank@kromann.info>
 * @author  David Coallier <davidc@php.net>
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Driver_Manager_odbc extends MDB2_Driver_Manager_Common
{
    // {{{ createDatabase()
    /**
     * create a new database
     *
     * @param string $name    name of the database that should be created
     * @param array  $options array with collation info
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createDatabase($name, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $name = $db->quoteIdentifier($name, true);
        $query = "CREATE DATABASE $name";
        if ($db->options['database_device']) {
            $query.= ' ON '.$db->options['database_device'];
            $query.= $db->options['database_size'] ? '=' .
                     $db->options['database_size'] : '';
        }
        if (!empty($options['collation'])) {
            $query .= ' COLLATE ' . $options['collation'];
        }
        return $db->standaloneQuery($query, null, true);
    }

    // }}}
    // {{{ alterDatabase()

    /**
     * alter an existing database
     *
     * @param string $name    name of the database that is intended to be changed
     * @param array  $options array with name, collation info
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function alterDatabase($name, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = '';
        if (!empty($options['name'])) {
            $query .= ' MODIFY NAME = ' .$db->quoteIdentifier($options['name'], true);
        }
        if (!empty($options['collation'])) {
            $query .= ' COLLATE ' . $options['collation'];
        }
        if (!empty($query)) {
            $query = 'ALTER DATABASE '. $db->quoteIdentifier($name, true) . $query;
            return $db->standaloneQuery($query, null, true);
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param string $name name of the database that should be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $name = $db->quoteIdentifier($name, true);
        return $db->standaloneQuery("DROP DATABASE $name", null, true);
    }

    // }}}
    // {{{ _getTemporaryTableQuery()

    /**
     * Override the parent method.
     *
     * @return string The string required to be placed between "CREATE" and "TABLE"
     *                to generate a temporary table, if possible.
     */
    function _getTemporaryTableQuery()
    {
        return '';
    }

    // }}}
    // {{{ _getAdvancedFKOptions()

    /**
     * Return the FOREIGN KEY query section dealing with non-standard options
     * as MATCH, INITIALLY DEFERRED, ON UPDATE, ...
     *
     * @param array $definition
     *
     * @return string
     * @access protected
     */
    function _getAdvancedFKOptions($definition)
    {
        $query = '';
        if (!empty($definition['onupdate'])) {
            $query .= ' ON UPDATE '.$definition['onupdate'];
        }
        if (!empty($definition['ondelete'])) {
            $query .= ' ON DELETE '.$definition['ondelete'];
        }
        return $query;
    }

    // }}}
    // {{{ createTable()

    /**
     * create a new table
     *
     * @param string $name   Name of the database that should be created
     * @param array  $fields Associative array that contains the definition of each field of the new table
     *                       The indexes of the array entries are the names of the fields of the table an
     *                       the array entry values are associative arrays like those that are meant to be
     *                       passed with the field definitions to get[Type]Declaration() functions.
     *
     *                      Example
     *                        array(
     *
     *                            'id' => array(
     *                                'type' => 'integer',
     *                                'unsigned' => 1,
     *                                'notnull' => 1,
     *                                'default' => 0,
     *                            ),
     *                            'name' => array(
     *                                'type' => 'text',
     *                                'length' => 12,
     *                            ),
     *                            'description' => array(
     *                                'type' => 'text',
     *                                'length' => 12,
     *                            )
     *                        );
     * @param array $options An associative array of table options:
     *                          array(
     *                              'comment' => 'Foo',
     *                              'temporary' => true|false,
     *                          );
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createTable($name, $fields, $options = array())
    {
        if (!empty($options['temporary'])) {
            $name = '#'.$name;
        }
        return parent::createTable($name, $fields, $options);
    }

    // }}}
    // {{{ truncateTable()

    /**
     * Truncate an existing table (if the TRUNCATE TABLE syntax is not supported,
     * it falls back to a DELETE FROM TABLE query)
     *
     * @param string $name name of the table that should be truncated
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function truncateTable($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $name = $db->quoteIdentifier($name, true);
        return $db->exec("TRUNCATE TABLE $name");
    }

    // }}}
    // {{{ vacuum()

    /**
     * Optimize (vacuum) all the tables in the db (or only the specified table)
     * and optionally run ANALYZE.
     *
     * @param string $table table name (all the tables if empty)
     * @param array  $options an array with driver-specific options:
     *               - timeout [int] (in seconds) [mssql-only]
     *               - analyze [boolean] [pgsql and mysql]
     *               - full [boolean] [pgsql-only]
     *               - freeze [boolean] [pgsql-only]
     *
     * NB: you have to run the NSControl Create utility to enable VACUUM
     *
     * @return mixed MDB2_OK success, a MDB2 error on failure
     * @access public
     */
    function vacuum($table = null, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $timeout = isset($options['timeout']) ? (int)$options['timeout'] : 300;

        $query = 'NSControl Create';
        $result = $db->exec($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        return $db->exec('EXEC NSVacuum '.$timeout);
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param string  $name    name of the table that is intended to be changed.
     * @param array   $changes associative array that contains the details of each type
     *                         of change that is intended to be performed. The types of
     *                         changes that are currently supported are defined as follows:
     *
     *                             name
     *
     *                                New name for the table.
     *
     *                            add
     *
     *                                Associative array with the names of fields to be added as
     *                                 indexes of the array. The value of each entry of the array
     *                                 should be set to another associative array with the properties
     *                                 of the fields to be added. The properties of the fields should
     *                                 be the same as defined by the MDB2 parser.
     *
     *
     *                            remove
     *
     *                                Associative array with the names of fields to be removed as indexes
     *                                 of the array. Currently the values assigned to each entry are ignored.
     *                                 An empty array should be used for future compatibility.
     *
     *                            rename
     *
     *                                Associative array with the names of fields to be renamed as indexes
     *                                 of the array. The value of each entry of the array should be set to
     *                                 another associative array with the entry named name with the new
     *                                 field name and the entry named Declaration that is expected to contain
     *                                 the portion of the field declaration already in DBMS specific SQL code
     *                                 as it is used in the CREATE TABLE statement.
     *
     *                            change
     *
     *                                Associative array with the names of the fields to be changed as indexes
     *                                 of the array. Keep in mind that if it is intended to change either the
     *                                 name of a field and any other properties, the change array entries
     *                                 should have the new names of the fields as array indexes.
     *
     *                                The value of each entry of the array should be set to another associative
     *                                 array with the properties of the fields to that are meant to be changed as
     *                                 array entries. These entries should be assigned to the new values of the
     *                                 respective properties. The properties of the fields should be the same
     *                                 as defined by the MDB2 parser.
     *
     *                            Example
     *                                array(
     *                                    'name' => 'userlist',
     *                                    'add' => array(
     *                                        'quota' => array(
     *                                            'type' => 'integer',
     *                                            'unsigned' => 1
     *                                        )
     *                                    ),
     *                                    'remove' => array(
     *                                        'file_limit' => array(),
     *                                        'time_limit' => array()
     *                                    ),
     *                                    'change' => array(
     *                                        'name' => array(
     *                                            'length' => '20',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 20,
     *                                            ),
     *                                        )
     *                                    ),
     *                                    'rename' => array(
     *                                        'sex' => array(
     *                                            'name' => 'gender',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 1,
     *                                                'default' => 'M',
     *                                            ),
     *                                        )
     *                                    )
     *                                )
     *
     * @param boolean $check   indicates whether the function should just check if the DBMS driver
     *                         can perform the requested table alterations if the value is true or
     *                         actually perform them otherwise.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $name_quoted = $db->quoteIdentifier($name, true);

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'remove':
            case 'rename':
            case 'add':
            case 'change':
            case 'name':
                break;
            default:
                return $db->raiseError(MDB2_ERROR_CANNOT_ALTER, null, null,
                    'change type "'.$change_name.'" not yet supported', __FUNCTION__);
            }
        }

        if ($check) {
            return MDB2_OK;
        }

        $idxname_format = $db->getOption('idxname_format');
        $db->setOption('idxname_format', '%s');

        if (!empty($changes['remove']) && is_array($changes['remove'])) {
            $result = $this->_dropConflictingIndices($name, array_keys($changes['remove']));
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }
            $result = $this->_dropConflictingConstraints($name, array_keys($changes['remove']));
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }

            $query = '';
            foreach ($changes['remove'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $field_name = $db->quoteIdentifier($field_name, true);
                $query.= 'COLUMN ' . $field_name;
            }

            $result = $db->exec("ALTER TABLE $name_quoted DROP $query");
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }
        }

        if (!empty($changes['rename']) && is_array($changes['rename'])) {
            foreach ($changes['rename'] as $field_name => $field) {
                $field_name = $db->quoteIdentifier($field_name, true);
                $result = $db->exec("sp_rename '$name_quoted.$field_name', '".$field['name']."', 'COLUMN'");
                if (PEAR::isError($result)) {
                    $db->setOption('idxname_format', $idxname_format);
                    return $result;
                }
            }
        }

        if (!empty($changes['add']) && is_array($changes['add'])) {
            $query = '';
            foreach ($changes['add'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                } else {
                    $query.= 'ADD ';
                }
                $query.= $db->getDeclaration($field['type'], $field_name, $field);
            }

            $result = $db->exec("ALTER TABLE $name_quoted $query");
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }
        }

        $dropped_indices     = array();
        $dropped_constraints = array();

        if (!empty($changes['change']) && is_array($changes['change'])) {
            $dropped = $this->_dropConflictingIndices($name, array_keys($changes['change']));
            if (PEAR::isError($dropped)) {
                $db->setOption('idxname_format', $idxname_format);
                return $dropped;
            }
            $dropped_indices = array_merge($dropped_indices, $dropped);
            $dropped = $this->_dropConflictingConstraints($name, array_keys($changes['change']));
            if (PEAR::isError($dropped)) {
                $db->setOption('idxname_format', $idxname_format);
                return $dropped;
            }
            $dropped_constraints = array_merge($dropped_constraints, $dropped);

            foreach ($changes['change'] as $field_name => $field) {
                //MSSQL doesn't allow multiple ALTER COLUMNs in one query
                $query = 'ALTER COLUMN ';

                //MSSQL doesn't allow changing the DEFAULT value of a field in altering mode
                if (array_key_exists('default', $field['definition'])) {
                    unset($field['definition']['default']);
                }

                $query .= $db->getDeclaration($field['definition']['type'], $field_name, $field['definition']);
                $result = $db->exec("ALTER TABLE $name_quoted $query");
                if (PEAR::isError($result)) {
                    $db->setOption('idxname_format', $idxname_format);
                    return $result;
                }
            }
        }

        // restore the dropped conflicting indices and constraints
        foreach ($dropped_indices as $index_name => $index) {
            $result = $this->createIndex($name, $index_name, $index);
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }
        }
        foreach ($dropped_constraints as $constraint_name => $constraint) {
            $result = $this->createConstraint($name, $constraint_name, $constraint);
            if (PEAR::isError($result)) {
                $db->setOption('idxname_format', $idxname_format);
                return $result;
            }
        }

        $db->setOption('idxname_format', $idxname_format);

        if (!empty($changes['name'])) {
            $new_name = $db->quoteIdentifier($changes['name'], true);
            $result = $db->exec("sp_rename '$name_quoted', '$new_name'");
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ _dropConflictingIndices()

    /**
     * Drop the indices that prevent a successful ALTER TABLE action
     *
     * @param string $table  table name
     * @param array  $fields array of names of the fields affected by the change
     *
     * @return array dropped indices definitions
     */
    function _dropConflictingIndices($table, $fields)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $dropped = array();
        $index_names = $this->listTableIndexes($table);
        if (PEAR::isError($index_names)) {
            return $index_names;
        }
        $db->loadModule('Reverse');
        $indexes = array();
        foreach ($index_names as $index_name) {
        	$idx_def = $db->reverse->getTableIndexDefinition($table, $index_name);
            if (!PEAR::isError($idx_def)) {
                $indexes[$index_name] = $idx_def;
            }
        }
        foreach ($fields as $field_name) {
            foreach ($indexes as $index_name => $index) {
                if (!isset($dropped[$index_name]) && array_key_exists($field_name, $index['fields'])) {
                    $dropped[$index_name] = $index;
                    $result = $this->dropIndex($table, $index_name);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
            }
        }

        return $dropped;
    }

    // }}}
    // {{{ _dropConflictingConstraints()

    /**
     * Drop the constraints that prevent a successful ALTER TABLE action
     *
     * @param string $table  table name
     * @param array  $fields array of names of the fields affected by the change
     *
     * @return array dropped constraints definitions
     */
    function _dropConflictingConstraints($table, $fields)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $dropped = array();
        $constraint_names = $this->listTableConstraints($table);
        if (PEAR::isError($constraint_names)) {
            return $constraint_names;
        }
        $db->loadModule('Reverse');
        $constraints = array();
        foreach ($constraint_names as $constraint_name) {
        	$cons_def = $db->reverse->getTableConstraintDefinition($table, $constraint_name);
            if (!PEAR::isError($cons_def)) {
                $constraints[$constraint_name] = $cons_def;
            }
        }
        foreach ($fields as $field_name) {
            foreach ($constraints as $constraint_name => $constraint) {
                if (!isset($dropped[$constraint_name]) && array_key_exists($field_name, $constraint['fields'])) {
                    $dropped[$constraint_name] = $constraint;
                    $result = $this->dropConstraint($table, $constraint_name);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
            }
            // also drop implicit DEFAULT constraints
            $default = $this->_getTableFieldDefaultConstraint($table, $field_name);
            if (!PEAR::isError($default) && !empty($default)) {
                $result = $this->dropConstraint($table, $default);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        return $dropped;
    }

    // }}}
    // {{{ _getTableFieldDefaultConstraint()

    /**
     * Get the default constraint for a table field
     *
     * @param string $table name of table that should be used in method
     * @param string $field name of field that should be used in method
     *
     * @return mixed name of default constraint on success, a MDB2 error on failure
     * @access private
     */
    function _getTableFieldDefaultConstraint($table, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table = $db->quoteIdentifier($table, true);
        $field = $db->quote($field, 'text');
        $query = "SELECT OBJECT_NAME(syscolumns.cdefault)
                    FROM syscolumns
                   WHERE syscolumns.id = object_id('$table')
                     AND syscolumns.name = $field
                     AND syscolumns.cdefault <> 0";
        return $db->queryOne($query);
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed array of table names on success, a MDB2 error on failure
     * @access public
     */
    function listTables()
    {
        $db =& $this->getDBInstance();

        if (PEAR::isError($db)) {
            return $db;
        }
        $res = odbc_tables($db->connection);
        $result = array();
		while (odbc_fetch_row($res)){
     		if(odbc_result($res,"TABLE_TYPE")=="TABLE") {
     			$table_name = odbc_result($res,"TABLE_NAME");
				if (!$this->_fixSequenceName($table_name, true)) {
                	$result[] = $table_name;
            	}     			
     		}
		}

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ?
                        'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a table in the current database
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of field names on success, a MDB2 error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $rs = odbc_columns($db->connection, "%", "%", $table);
        $columns = array();
        while($data = odbc_fetch_array($rs)) {
        	$columns[] = $data[COLUMN_NAME];
        }
        
        /*
        throw new Exception(); 
        
        $table = $db->quoteIdentifier($table, true);
        $columns = $db->queryCol("SELECT c.name
                                    FROM syscolumns c
                               LEFT JOIN sysobjects o ON c.id = o.id
                                   WHERE o.name = '$table'");
        if (PEAR::isError($columns)) {
            return $columns;
        }
        */
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $columns);
        }
        return $columns;
    }

    // }}}
    // {{{ listTableIndexes()

    /**
     * list all indexes in a table
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of index names on success, a MDB2 error on failure
     * @access public
     */
    function listTableIndexes($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $key_name = 'INDEX_NAME';
        $pk_name = 'PK_NAME';
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
                $pk_name  = strtolower($pk_name);
            } else {
                $key_name = strtoupper($key_name);
                $pk_name  = strtoupper($pk_name);
            }
        }
        
        //$table = $db->quote($table, 'text');
        //$query = "EXEC sp_statistics @table_name=$table";
        $table = "V_PARTNERSTAMM";
        $res = odbc_statistics($db->connection,$db->dsn[username],$db->dsn[username],$table,0,0);
        $indexes = array();
        while($data = odbc_fetch_array($res)) {
        	$indexes[] = $data[$key_name];
        }

        
        $res = odbc_primarykeys($db->connection,$db->dsn[username],$db->dsn[username],$table);
        
        
        $pk_all = array();
        while($data = odbc_fetch_array($res)) {
        	$pk_all[] = $data[$key_name];
        }

        $result = array();
        foreach ($indexes as $index) {
            if (!in_array($index, $pk_all) && ($index = $this->_fixIndexName($index))) {
                $result[$index] = true;
            }
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }

    // }}}
    // {{{ listDatabases()

    /**
     * list all databases
     *
     * @return mixed array of database names on success, a MDB2 error on failure
     * @access public
     */
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->queryCol('SELECT name FROM sys.databases');
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listUsers()

    /**
     * list all users
     *
     * @return mixed array of user names on success, a MDB2 error on failure
     * @access public
     */
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->queryCol('SELECT DISTINCT loginame FROM master..sysprocesses');
        if (PEAR::isError($result) || empty($result)) {
            return $result;
        }
        foreach (array_keys($result) as $k) {
            $result[$k] = trim($result[$k]);
        }
        return $result;
    }

    // }}}
    // {{{ listFunctions()

    /**
     * list all functions in the current database
     *
     * @return mixed array of function names on success, a MDB2 error on failure
     * @access public
     */
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name
                    FROM sysobjects
                   WHERE objectproperty(id, N'IsMSShipped') = 0
                    AND (objectproperty(id, N'IsTableFunction') = 1
                     OR objectproperty(id, N'IsScalarFunction') = 1)";
        /*
        SELECT ROUTINE_NAME
          FROM INFORMATION_SCHEMA.ROUTINES
         WHERE ROUTINE_TYPE = 'FUNCTION'
        */
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listTableTriggers()

    /**
     * list all triggers in the database that reference a given table
     *
     * @param string table for which all referenced triggers should be found
     *
     * @return mixed array of trigger names on success,  otherwise, false which
     *               could be a db error if the db is not instantiated or could
     *               be the results of the error that occured during the
     *               querying of the sysobject module.
     * @access public
     */
    function listTableTriggers($table = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table = $db->quote($table, 'text');
        $query = "SELECT o.name
                    FROM sysobjects o
                   WHERE xtype = 'TR'
                     AND OBJECTPROPERTY(o.id, 'IsMSShipped') = 0";
        if (!is_null($table)) {
            $query .= " AND object_name(parent_obj) = $table";
        }

        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE &&
            $db->options['field_case'] == CASE_LOWER)
        {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ?
                'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listViews()

    /**
     * list all views in the current database
     *
     * @param string database, the current is default
     *
     * @return mixed array of view names on success, a MDB2 error on failure
     * @access public
     */
    function listViews()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $res = odbc_tables($db->connection);
        $result = array();
		while (odbc_fetch_row($res)){
     		if(odbc_result($res,"TABLE_TYPE")=="VIEW") {
     			$table_name = odbc_result($res,"TABLE_NAME");
				if (!$this->_fixSequenceName($table_name, true)) {
                	$result[] = $table_name;
            	}     			
     		}
		}

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE &&
            $db->options['field_case'] == CASE_LOWER)
        {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ?
                          'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ dropIndex()

    /**
     * drop existing index
     *
     * @param string $table name of table that should be used in method
     * @param string $name  name of the index to be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropIndex($table, $name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table = $db->quoteIdentifier($table, true);
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        return $db->exec("DROP INDEX $table.$name");
    }

    // }}}
    // {{{ listTableConstraints()

    /**
     * list all constraints in a table
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of constraint names on success, a MDB2 error on failure
     * @access public
     */
    function listTableConstraints($table)
    {
    	return array();
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);

        $query = "SELECT c.constraint_name
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS c
                   WHERE c.constraint_catalog = DB_NAME()
                     AND c.table_name = '$table'";
        $constraints = $db->queryCol($query);
        if (PEAR::isError($constraints)) {
            return $constraints;
        }

        $result = array();
        foreach ($constraints as $constraint) {
            $constraint = $this->_fixIndexName($constraint);
            if (!empty($constraint)) {
                $result[$constraint] = true;
            }
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param string $seq_name name of the sequence to be created
     * @param string $start    start value of the sequence; default is 1
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->quoteIdentifier($db->getSequenceName($seq_name), true);
        $seqcol_name = $db->quoteIdentifier($db->options['seqcol_name'], true);
        $query = "CREATE TABLE $sequence_name ($seqcol_name " .
                 "INT PRIMARY KEY CLUSTERED IDENTITY($start,1) NOT NULL)";

        $res = $db->exec($query);
        if (PEAR::isError($res)) {
            return $res;
        }

        $query = "SET IDENTITY_INSERT $sequence_name ON ".
                 "INSERT INTO $sequence_name ($seqcol_name) VALUES ($start)";
        $res = $db->exec($query);

        if (!PEAR::isError($res)) {
            return MDB2_OK;
        }

        $result = $db->exec("DROP TABLE $sequence_name");
        if (PEAR::isError($result)) {
            return $db->raiseError($result, null, null,
                'could not drop inconsistent sequence table', __FUNCTION__);
        }

        return $db->raiseError($res, null, null,
            'could not create sequence table', __FUNCTION__);
    }

    // }}}
    // {{{ dropSequence()

    /**
     * This function drops an existing sequence
     *
     * @param string $seq_name name of the sequence to be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->quoteIdentifier($db->getSequenceName($seq_name), true);
        return $db->exec("DROP TABLE $sequence_name");
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed array of sequence names on success, a MDB2 error on failure
     * @access public
     */
    function listSequences()
    {
    	return array();
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sysobjects WHERE xtype = 'U'";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        foreach ($table_names as $table_name) {
            if ($sqn = $this->_fixSequenceName($table_name, true)) {
                $result[] = $sqn;
            }
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ?
                          'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
}

// }}}
?>