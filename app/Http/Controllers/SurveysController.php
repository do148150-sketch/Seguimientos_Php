<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyVote;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SurveysController extends Controller
{
    public function index() {

        // withCount evita una consulta por encuesta para contar sus votos
        $surveys = Survey::withCount(['options', 'votes'])
                         ->orderByDesc('id')
                         ->get();

        return view('surveys/index', [ 'surveys' => $surveys ]);
    }

    /**
     * Ficha de la encuesta: el formulario para votar, o los resultados
     * si este visitante ya votó o la encuesta está cerrada.
     */
    public function show(Request $request, $id) {

        $survey = Survey::with('options')->find($id);

        if (empty($survey)) {

            Session::flash('message', [ 'content' => "La encuesta con id '$id' no existe.", 'type' => 'error' ]);
            return redirect()->route('surveys.index');
        }

        $token = $this->voterToken($request);

        $myVote = SurveyVote::where('survey_id', $survey->id)
                            ->where('voter_token', $token)
                            ->first();

        // Votos por opción, en una sola consulta agrupada
        $tally = SurveyVote::where('survey_id', $survey->id)
                           ->select('survey_option_id', DB::raw('COUNT(*) as total'))
                           ->groupBy('survey_option_id')
                           ->pluck('total', 'survey_option_id');

        $totalVotes = $tally->sum();

        // Se muestran los resultados si ya votó, si está cerrada o si lo pide la URL
        $showResults = $myVote !== null
                    || !$survey->is_open
                    || $request->boolean('results');

        return view('surveys/show', [
            'survey'      => $survey,
            'tally'       => $tally,
            'totalVotes'  => $totalVotes,
            'myVote'      => $myVote,
            'showResults' => $showResults,
        ]);
    }

    public function store(Request $request) {

        Validator::make($request->all(),
        [
            'question'  => 'required|max:160',
            'options'   => 'required|array|min:2|max:6',
            'options.*' => 'nullable|max:80',
        ],
        [
            'question.required' => 'La pregunta de la encuesta es requerida',
            'question.max'      => 'La pregunta no puede ser mayor a :max caracteres',
            'options.required'  => 'La encuesta necesita opciones',
            'options.min'       => 'La encuesta necesita al menos :min opciones',
            'options.max'       => 'La encuesta no puede tener más de :max opciones',
            'options.*.max'     => 'Cada opción no puede ser mayor a :max caracteres',
        ])->validate();

        // Se descartan las casillas que quedaron vacías
        $options = array_values(array_filter(
            array_map('trim', $request->input('options')),
            fn ($label) => $label !== ''
        ));

        if (count($options) < 2) {

            Session::flash('message', [ 'content' => "La encuesta necesita al menos 2 opciones con texto.", 'type' => 'error' ]);
            return redirect()->back()->withInput();
        }

        try {

            // La encuesta y sus opciones se guardan juntas o no se guarda nada
            $survey = DB::transaction(function () use ($request, $options) {

                $survey = new Survey();
                $survey->question = $request->question;
                $survey->is_open  = true;
                $survey->save();

                foreach ($options as $label) {

                    $option = new SurveyOption();
                    $option->survey_id = $survey->id;
                    $option->label     = $label;
                    $option->save();
                }

                return $survey;
            });

            Session::flash('message', [ 'content' => "Encuesta creada con éxito", 'type' => 'success' ]);
            return redirect()->route('surveys.show', $survey->id);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function vote(Request $request, $id) {

        Validator::make($request->all(),
        [
            'survey_option_id' => 'required|exists:survey_options,id',
        ],
        [
            'survey_option_id.required' => 'Debes elegir una opción',
            'survey_option_id.exists'   => 'La opción seleccionada no existe',
        ])->validate();

        try {

            $survey = Survey::find($id);

            if (empty($survey)) {

                Session::flash('message', [ 'content' => "La encuesta con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->route('surveys.index');
            }

            if (!$survey->is_open) {

                Session::flash('message', [ 'content' => "Esta encuesta ya está cerrada.", 'type' => 'error' ]);
                return redirect()->route('surveys.show', $survey->id);
            }

            // La opción tiene que pertenecer a ESTA encuesta
            $option = SurveyOption::where('id', $request->survey_option_id)
                                  ->where('survey_id', $survey->id)
                                  ->first();

            if (empty($option)) {

                Session::flash('message', [ 'content' => "Esa opción no pertenece a esta encuesta.", 'type' => 'error' ]);
                return redirect()->route('surveys.show', $survey->id);
            }

            $token = $this->voterToken($request);

            // Un voto por encuesta y por visitante. La BD lo respalda con un UNIQUE.
            $yaVoto = SurveyVote::where('survey_id', $survey->id)
                                ->where('voter_token', $token)
                                ->exists();

            if ($yaVoto) {

                Session::flash('message', [ 'content' => "Ya habías votado en esta encuesta.", 'type' => 'error' ]);
                return redirect()->route('surveys.show', $survey->id);
            }

            $vote = new SurveyVote();
            $vote->survey_id        = $survey->id;
            $vote->survey_option_id = $option->id;
            $vote->voter_token      = $token;
            $vote->save();

            Session::flash('message', [ 'content' => "¡Voto registrado! Estos son los resultados.", 'type' => 'success' ]);
            return redirect()->route('surveys.show', $survey->id);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    /**
     * Abre o cierra la encuesta. Cerrada, deja de admitir votos
     * pero sus resultados se siguen viendo.
     */
    public function toggle($id) {

        try {

            $survey = Survey::find($id);

            if (empty($survey)) {

                Session::flash('message', [ 'content' => "La encuesta con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $survey->is_open = !$survey->is_open;
            $survey->save();

            $texto = $survey->is_open ? "Encuesta reabierta" : "Encuesta cerrada";

            Session::flash('message', [ 'content' => $texto, 'type' => 'success' ]);
            return redirect()->back();

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $survey = Survey::find($id);

            if (empty($survey)) {

                Session::flash('message', [ 'content' => "La encuesta con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            // Las opciones y los votos se borran solos: la migración
            // declara las llaves foráneas con cascadeOnDelete
            $survey->delete();

            Session::flash('message', [ 'content' => "Encuesta eliminada con éxito", 'type' => 'success' ]);
            return redirect()->route('surveys.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    /**
     * Identifica al visitante sin pedirle datos: un token aleatorio
     * guardado en su sesion. Sirve para que no vote dos veces.
     */
    private function voterToken(Request $request): string {

        if (!$request->session()->has('voter_token')) {
            $request->session()->put('voter_token', (string) Str::uuid());
        }

        return $request->session()->get('voter_token');
    }
}
