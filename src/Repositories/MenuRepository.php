<?php namespace CleanSoft\Modules\Core\Menu\Repositories;

use Illuminate\Support\Facades\DB;
use CleanSoft\Modules\Core\Menu\Models\Menu;
use CleanSoft\Modules\Core\Models\Contracts\BaseModelContract;
use CleanSoft\Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuNodeRepositoryContract;
use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuRepositoryContract;

class MenuRepository extends EloquentBaseRepository implements MenuRepositoryContract
{
    /**
     * @var MenuNodeRepository|MenuNodeRepositoryCacheDecorator
     */
    protected $menuNodeRepository;

    public function __construct(BaseModelContract $model)
    {
        parent::__construct($model);

        $this->menuNodeRepository = app(MenuNodeRepositoryContract::class);
    }

    /**
     * @param array $data
     * @param array|null $menuStructure
     * @return int|null
     */
    public function createMenu(array $data, array $menuStructure = null)
    {
        $result = $this->create($data);
        if (!$result || !$menuStructure) {
            return $result;
        }
        DB::beginTransaction();
        if ($menuStructure !== null) {
            $this->updateMenuStructure($result, $menuStructure);
        }
        DB::commit();
        return $result;
    }

    /**
     * @param Menu|int $id
     * @param array $data
     * @param array|null $menuStructure
     * @param array|null $deletedNodes
     * @return int|null
     */
    public function updateMenu($id, array $data, array $menuStructure = null, array $deletedNodes = null)
    {
        $result = $this->update($id, $data);

        if (!$result || !$menuStructure) {
            return $result;
        }
        DB::beginTransaction();
        if($deletedNodes) {
            $this->menuNodeRepository->delete($deletedNodes);
        }

        if ($menuStructure !== null) {
            $this->updateMenuStructure($result, $menuStructure);
        }
        DB::commit();

        return $result;
    }

    /**
     * @param int $menuId
     * @param array $menuStructure
     */
    public function updateMenuStructure($menuId, array $menuStructure)
    {
        foreach ($menuStructure as $order => $node) {
            $this->menuNodeRepository->updateMenuNode($menuId, $node, $order);
        }
    }

    /**
     * @param Menu|int $id
     * @return \Illuminate\Database\Eloquent\Builder|null|Menu|\CleanSoft\Modules\Core\Models\EloquentBase
     */
    public function getMenu($id)
    {
        if($id instanceof Menu) {
            $menu = $id;
        } else {
            $menu = $this->find($id);
        }
        if(!$menu) {
            return null;
        }

        $menu->all_menu_nodes = $this->menuNodeRepository->getMenuNodes($menu);

        return $menu;
    }
}
