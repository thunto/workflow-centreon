<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostGroupRelationRepository extends ServiceEntityRepository
{

    /**
     * Export host's groups
     * 
     * @param int $pollerId
     * @param array $templateChainList
     * @return array
     */
    public function export(int $pollerId, array $templateChainList = null): array
    {
        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM hostgroup_relation AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.hgr_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM hostgroup_relation AS tt
WHERE tt.host_host_id IN ({$list})
GROUP BY tt.hgr_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.hgr_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
