<?php
namespace core\lib\DB;

interface IDbInfo{
    function query($sql);
    function form($tableName);
    function where($_where = '1=1');
    function order($_order = 'id DESC');
    function limit($_limit = 30);
    function select($_select = '*');
    function insert($arr = []);
    function delete();
    function save($arr = []);
}