<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ExpensesController extends Controller
{
    /**
     * Categorias fijas del ejercicio. Estan aqui y no en la vista para que
     * el formulario y la validacion lean siempre la misma lista.
     */
    public const CATEGORIES = [
        'Alimentación',
        'Transporte',
        'Vivienda',
        'Salud',
        'Educación',
        'Entretenimiento',
        'Otros',
    ];

    public function index(Request $request) {

        // Mes que se esta viendo. Por defecto el actual.
        $month = $request->input('month', date('Y-m'));

        // El mes llega por la URL, asi que no se puede confiar en el formato
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = date('Y-m');
        }

        [$year, $monthNumber] = explode('-', $month);

        $expenses = Expense::whereYear('spent_on', $year)
                           ->whereMonth('spent_on', $monthNumber)
                           ->orderByDesc('spent_on')
                           ->orderByDesc('id')
                           ->get();

        $total = $expenses->sum('amount');

        // Resumen por categoria: cuanto pesa cada una dentro del mes
        $summary = $expenses->groupBy('category')
                            ->map(fn ($group) => $group->sum('amount'))
                            ->sortDesc();

        // Comparativo de los ultimos 6 meses. Se agrupa en PHP y no con
        // DATE_FORMAT para no amarrar la consulta a un motor concreto.
        $since = Carbon::createFromFormat('Y-m-d', "$month-01")
                       ->startOfMonth()
                       ->subMonths(5);

        $trend = Expense::where('spent_on', '>=', $since->toDateString())
                        ->get()
                        ->groupBy(fn ($expense) => $expense->spent_on->format('Y-m'))
                        ->map(fn ($group) => $group->sum('amount'))
                        ->sortKeys();

        return view('expenses/index', [
            'expenses'   => $expenses,
            'total'      => $total,
            'summary'    => $summary,
            'trend'      => $trend,
            'month'      => $month,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request) {

        Validator::make($request->all(),
        [
            'description' => 'required|max:120',
            'amount'      => 'required|numeric|min:1|max:999999999',
            'category'    => 'required|in:' . implode(',', self::CATEGORIES),
            'spent_on'    => 'required|date|before_or_equal:today',
        ],
        [
            'description.required' => 'La descripción del gasto es requerida',
            'description.max'      => 'La descripción no puede ser mayor a :max caracteres',
            'amount.required'      => 'El valor del gasto es requerido',
            'amount.numeric'       => 'El valor del gasto debe ser un número',
            'amount.min'           => 'El valor del gasto debe ser mayor que cero',
            'amount.max'           => 'El valor del gasto es demasiado grande',
            'category.required'    => 'La categoría es requerida',
            'category.in'          => 'La categoría seleccionada no es válida',
            'spent_on.required'    => 'La fecha del gasto es requerida',
            'spent_on.date'        => 'La fecha del gasto no es válida',
            'spent_on.before_or_equal' => 'No se pueden registrar gastos con fecha futura',
        ])->validate();

        try {

            $expense = new Expense();
            $expense->description = $request->description;
            $expense->amount      = $request->amount;
            $expense->category    = $request->category;
            $expense->spent_on    = $request->spent_on;
            $expense->save();

            Session::flash('message', [ 'content' => "Gasto registrado con éxito", 'type' => 'success' ]);

            // Vuelve al mes del gasto recien creado, no al mes actual
            return redirect()->route('expenses.index', [ 'month' => substr($request->spent_on, 0, 7) ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $expense = Expense::find($id);

            if (empty($expense)) {

                Session::flash('message', [ 'content' => "El gasto con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $month = $expense->spent_on->format('Y-m');
            $expense->delete();

            Session::flash('message', [ 'content' => "Gasto eliminado con éxito", 'type' => 'success' ]);
            return redirect()->route('expenses.index', [ 'month' => $month ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }
}
