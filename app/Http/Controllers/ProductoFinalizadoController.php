<?php

namespace App\Http\Controllers;

use App\Models\producto_finalizado;
use App\Models\mano_obra_has_producto_f;
use App\Models\mano_de_obra;
use App\Models\materia_prima;
use App\Models\producto_a_fabricar;
use App\Models\hp_producto_finalizado_materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use DB;

class ProductoFinalizadoController extends Controller
{
    //Colocamos el middleware
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $datos['producto_finalizados']=producto_finalizado::orderBy('id','DESC')->paginate(10);
        return view('producto_finalizado.index',$datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(producto_a_fabricar $producto_a_fabricar)
    {
        $materia_prima['materia_prima']=materia_prima::all();
        $mano_de_obra['mano_de_obra']=mano_de_obra::all();
        return View::make('producto_finalizado.create' ,compact('producto_a_fabricar'))->
        with($materia_prima)->
        with($mano_de_obra);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,producto_a_fabricar $producto_a_fabricar )
    {
        $input  = $request->all();
        //dd($input );
        try{
            DB::beginTransaction();
            $productoFinalizado = producto_finalizado::create([
                "c_agua" =>$input["c_agua" ],
                "c_luz" =>$input["c_luz"],
                "c_varios"=>$input["c_varios"],
                "c_admin" => $input["c_admin"],
                "c_imprevistos"=>$input["c_imprevistos"],
                "c_total"=>$input["c_total"],
                "c_utilidad"=>$input["c_utilidad"],
                "c_iva"=>$input["c_iva"],
                "total"=>$input["total"],
                "estado"=>"Undelivered",
                "cliente_id"=>$producto_a_fabricar->cliente_id,
                "id_orden"=>$producto_a_fabricar->id
            ]);
            foreach($input["insumos_id"] as $key =>$value ){
                hp_producto_finalizado_materia::create([
                    'materia_prima_id'=> $value,
                    'producto_finalizado_id'=> $productoFinalizado->id,
                    'cantidad'=>$input["cantidadeses"][$key],
                ]);
                $materiaActua= materia_prima::findOrFail($value);
                materia_prima::where('id','=', $value)->update(['cantidad_mp' => $materiaActua->cantidad_mp - $input["cantidadeses"][$key]] );

            }
            foreach($input["insumo_id"] as $key =>$value ){
                    mano_obra_has_producto_f::create([
                        'mano_de_obra_id'=> $value,
                        'horas' => $input["cantidades"][$key],
                        'horas_costo'=> $input["costos"][$key],
                        'producto_finalizado_id'=> $productoFinalizado->id,
                    ]);

                }
        DB::commit();
        producto_a_fabricar::where('id', '=',$producto_a_fabricar->id)->update(['estado' => 'Finalizado']);
        //restando en stok

        return redirect("producto_finalizado")->with('status','1');

        }catch(\Exception $e){
            DB::rollBack();
            return redirect("producto_finalizado")->with('status',$e->getMessage());
        }
    }

    public function calcular_total(producto_finalizado $data){
        $suma = 0;
        foreach ($data->mano_obra_has_producto_f as $menu) {
           $suma+= $menu->precio_hora;
       }
        return $suma;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\producto_finalizado  $producto_finalizado
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $datos=producto_finalizado::find($id);
        $data[]= $datos->mano_obra_has_producto_f;
        $dataMateria[]= $datos->hp_producto_finalizado_materia;
        $total=$this->calcular_total($datos);
        return View::make('producto_finalizado.show', compact('data','datos','dataMateria'))->with('total',$total);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\producto_finalizado  $producto_finalizado
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $producto_finalizado =producto_finalizado::findOrFail($id);
        $mano_de_obra['mano_de_obra']=mano_de_obra::all();
        return View::make('producto_finalizado.edit',compact('producto_finalizado'))->with($mano_de_obra);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\producto_finalizado  $producto_finalizado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Validación de datos
        $campos=[

            'c_agua'=>'numeric|min:0|nullable',
            'c_luz'=>'numeric|min:0|nullable',
            'c_varios'=>'numeric|min:0|nullable',
            'c_admin'=>'numeric|min:0|nullable',
            'c_imprevistos'=>'numeric|min:0|nullable',
            'c_total'=>'numeric|min:0|nullable',
            'c_utilidad'=>'numeric|min:0|nullable',
            'c_iva'=>'numeric|min:0|nullable',
            'total'=>'numeric|min:0|nullable',

        ];
        $mensaje=[
            'required'=>'El :attribute es requerido',
        ];
        $mensaje=[
            'numeric'=>'El :attribute tiene que ser un número',
        ];

        $this->validate($request, $campos, $mensaje);

        $datosProducto_Finalizado = request()->except(['_token','_method']);

        producto_finalizado::where('id','=',$id)->update($datosProducto_Finalizado);
        $producto_finalizado=producto_finalizado::findOrFail($id);
        return redirect('producto_finalizado')->with('mensaje','Producto modificado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\producto_finalizado  $producto_finalizado
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //se esta recepcionando el id del formulario del index
        $producto_finalizado=producto_finalizado::findOrFail($id);
        producto_finalizado::destroy($id);
        return redirect('producto_finalizado')->with('mensaje','Producto  eliminado');
    }
}
