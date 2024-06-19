<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Bag;
use OpenApi\Annotations as OA;

/**
 * Class BagController.
 * 
 * @author Yonathan <yonathan.422023017@civitas.ukrida.ac.id>
 */
class BagController extends Controller
{
    /** 
     * @OA\Get(
     *     path="/api/bag",
     *     tags={"bag"},
     *     summary="Display a listing of the items",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="successful",
     *         @OA\JsonContent()
     *     ),
     *  @OA\Parameter(
     *      name="_page",
     *      in="query",
     *      description="current page",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *          format="int64",
     *          example=1
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="_limit",
     *      in="query",
     *      description="max item in a page",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *          format="int64",
     *          example=10
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="_search",
     *      in="query",
     *      description="word to search",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="_publisher",
     *      in="query",
     *      description="search by publisher like name",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="_sort_by",
     *      in="query",
     *      description="word to search",
     *      required=false,
     *      @OA\Schema(
     *          type="integer",
     *          example="6"
     *      )
     *  ),
     * )
     */
    public function index(Request $request)
    {
        try {
            $data['filter']       = $request->all();
            $page                 = $data['filter']['_page']  = (@$data['filter']['_page'] ? intval($data['filter']['_page']) : 1);
            $limit                = $data['filter']['_limit'] = (@$data['filter']['_limit'] ? intval($data['filter']['_limit']) : 1000);
            $offset               = ($page?($page-1)*$limit:0);
            $data['products']     = Bag::whereRaw('1 = 1');
            
            if($request->get('_search')){
                $data['products'] = $data['products']->whereRaw('(LOWER(name) LIKE "%'.strtolower($request->get('_search')).'%")');
            }
            if($request->get('_designer')){
                $data['products'] = $data['products']->whereRaw('LOWER(designer) = "'.strtolower($request->get('_designer')).'"');
            }
            if($request->get('_sort_by')){
            switch ($request->get('_sort_by')) {
                default:
                case 'latest_publication':
                $data['products'] = $data['products']->orderBy('publication_year','DESC');
                break;
                case 'latest_added':
                $data['products'] = $data['products']->orderBy('created_at','DESC');
                break;
                case 'title_asc':
                $data['products'] = $data['products']->orderBy('title','ASC');
                break;
                case 'title_desc':
                $data['products'] = $data['products']->orderBy('title','DESC');
                break;
                case 'price_asc':
                $data['products'] = $data['products']->orderBy('price','ASC');
                break;
                case 'price_desc':
                $data['products'] = $data['products']->orderBy('price','DESC');
                break;
            }
            }
            $data['products_count_total']   = $data['products']->count();
            $data['products']               = ($limit==0 && $offset==0)?$data['products']:$data['products']->limit($limit)->offset($offset);
            // $data['products_raw_sql']       = $data['products']->toSql();
            $data['products']               = $data['products']->get();
            $data['products_count_start']   = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+1);
            $data['products_count_end']     = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+sizeof($data['products']));
           return response()->json($data, 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }
    /**
     * @OA\Post(
     *      path="/api/bag",
     *      tags={"bag"},
     *      summary="Store a newly created item",
     *      operationId="store",
     *      @OA\Response(
     *           response=400,
     *           description="Invalid input",
     *           @OA\JsonContent()
     *       ),
     *       @OA\Response(
     *           response=201,
     *           description="Successful",
     *           @OA\JsonContent()
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           description="Request body description",
     *           @OA\JsonContent(
     *               ref="#/components/schemas/Bag",
     *               example={"name": "LA MEDUSA CANVAS LARGE TOTE BAG", "designer": "Gianni Versace", "publisher": "Versace", "publication_year": "2024",
     *                        "cover": "https://cdn-images.italist.com/image/upload/t_medium_dpr_2_q_auto_v_2,f_auto/ebcc1c021e598dcb630515710d2d2134.jpg",
     *                        "description": "Terinspirasi oleh mitologi Yunani, LA MEDUSA CANVAS LARGE TOTE BAG telah menjadi penanda utama rumah Versace. Dinamakan berdasarkan motif ikoniknya, tas jinjing berbahan kanvas La Medusa semakin dipertegas dengan logo timbul dan pegangan bagian atas berbahan kulit.",
     *                        "price": 26490000}
     *            ),
     *          ),
     *          security={{"passport_token_ready":{},"passport":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|unique:bags',
                'author' => 'required|max:100',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->message()->first());
            }
            $bag = new Bag;
            $bag->fill($request->all())->save();
            return $bag;

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bag/{id}",
     *     tags={"bag"},
     *     summary="Display the specified item",
     *     operationId="show",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of item that needs to be displayed",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     * )
     */
    public function show($id)
    {
        $bag = Bag::find($id);
        if(!$bag){
            throw new HttpException(404, 'Item not found');
        }
        return $bag;
    }

    /**
     * @OA\Put(
     *     path="/api/bag/{id}",
     *     tags={"bag"},
     *     summary="Update the specified item",
     *     operationId="update",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid input",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of item that needs to be updated",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           description="Request body description",
     *           @OA\JsonContent(
     *               ref="#/components/schemas/Bag",
     *               example={"name": "LA MEDUSA CANVAS LARGE TOTE BAG", "designer": "Gianni Versace", "publisher": "Versace", "publication_year": "2024",
     *                        "cover": "https://cdn-images.italist.com/image/upload/t_medium_dpr_2_q_auto_v_2,f_auto/ebcc1c021e598dcb630515710d2d2134.jpg",
     *                        "description": "Terinspirasi oleh mitologi Yunani, LA MEDUSA CANVAS LARGE TOTE BAG telah menjadi penanda utama rumah Versace. Dinamakan berdasarkan motif ikoniknya, tas jinjing berbahan kanvas La Medusa semakin dipertegas dengan logo timbul dan pegangan bagian atas berbahan kulit.",
     *                        "price": 26490000}
     *           ),
     *        ),
     *        security={{"passport_token_ready":{},"passport":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $bag = Bag::find($id);
        if(!$bag){
            throw new HttpException(404, 'Item not found');
        }

        try {
            $validator = Validator::make($request->all(), [
                'title'  => 'required|unique:bags',
                'author'  => 'required|max:100',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
            $bag->fill($request->all())->save();
            return response()->json(array('message'=>'Updated successfully'), 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/bag/{id}",
     *     tags={"bag"},
     *     summary="Remove the specified item",
     *     operationId="destroy",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be removed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *          )
     *      ),
     *      security={{"passport_token_ready":{}, "passport":{}}}
     * )
     */
    public function destroy($id)
    {
        $bag = Bag::find($id);
        if(!$bag){
            throw new HttpException(404, 'Item not found');
        }

        try {
            $bag->delete();
            return response()->json(array('message'=>'Deleted successfully'), 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }
}