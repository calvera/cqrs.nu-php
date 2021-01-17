<?php

declare(strict_types=1);

namespace Cafe\Infra\Read;

use Cafe\Application\Read\ChefTodoList\TodoListGroup;
use Cafe\Application\Read\ChefTodoList\TodoListItem;
use Cafe\Application\Read\ChefTodoListQueries;
use Doctrine\DBAL\Connection;

class ChefTodoListQueriesDBAL implements ChefTodoListQueries
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getTodoList(): array
    {
        $groups = $itemsByGroupId = [];
        $rowsItems = $this->connection->fetchAllAssociative(<<<'SQL'
select group_id	
      ,description	
      ,menu_number	
      ,t.tab_id as tab_id
      ,t.table_number as table_number
  from read_model_chef_todo_item i
  join read_model_tab t on t.tab_id = i.tab_id
SQL
);

        foreach ($rowsItems as $i => $row) {
            $itemsByGroupId[$row['group_id']][$i] = $row;
        }

        foreach ($itemsByGroupId as $groupId => $itemRows) {
            $items = [];
            foreach ($itemRows as $i => $itemRow) {
                $items[] = new TodoListItem((int)$itemRow['menu_number'], $itemRow['description']);
            }
            $groups[] = new TodoListGroup($groupId, $itemRows[$i]['tab_id'], (int)$itemRows[$i]['table_number'], $items);
        }

        return $groups;
    }
}