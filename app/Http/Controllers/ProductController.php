<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Mostrar una llista de productes.
     */
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json([
                'status' => true,
                'products' => $products,
                'message' => 'Llista de productes'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nou producte.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $product = Product::create($request->all());
            return response()->json([
                'status' => true,
                'data' => $product,
                'message' => 'Producte creat correctament'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al crear el producte'
            ], 500);
        }
    }

    /**
     * Mostrar un producte concret.
     */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json([
                'status' => true,
                'product' => $product,
                'message' => 'Producte trobat'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Producte no trobat'
            ], 404);
        }
    }

    /**
     * Actualitzar un producte concret.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error en la validació de dades',
            ], 422);
        }

        try {
            $product = Product::findOrFail($id);
            $product->update($request->all());
            return response()->json([
                'status' => true,
                'data' => $product,
                'message' => 'Producte actualitzat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualitzar el producte'
            ], 500);
        }
    }

    /**
     * Eliminar un producte concret.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json([
                'status' => true,
                'message' => 'Producte eliminat correctament'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el producte'
            ], 500);
        }
    }

    public function decrementStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->stock = max(0, $product->stock - 1); // Per evitar numeros negatius
        $product->save();

        return response()->json(['success' => true, 'stock' => $product->stock]);
    }

    public function incrementStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->stock += 1;
        $product->save();

        return response()->json(['success' => true, 'stock' => $product->stock]);
    }
}
