<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Asignacion;
use App\Models\Bloque;
use App\Models\ConceptoPago;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Asistencia;
use App\Models\Institucion;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Observacion;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\SolicitudMatricula;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewsController
{
    public function index()
    {
        return view('welcome');
    }

    public function features()
    {
        return view('features');
    }

    public function info()
    {
        return view('info');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function enroll()
    {
        $grupos = Grupo::with('institucion', 'grado')->get()->groupBy('institucion_id');

        return view('enroll', compact('grupos'));
    }

    public function dashboard()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $usuario = $usuarioSesion;

        return view('app.dashboard', compact('usuarioSesion', 'usuario'));
    }

    // Admin Views
    public function institutions()
    {
        $usuarioSesion = Auth::user()->load('rol');
        $search = request('search');

        $instituciones = Institucion::search($search ?? '')->paginate(5);

        return view('app.admin.institutions', compact('usuarioSesion', 'instituciones'));
    }

    public function users()
    {
        $usuarioSesion = Auth::user()->load('rol');
        $search = request('search');

        $usuarios = Usuario::with('administrativo', 'administrativo.permisos', 'administrativo.institucion', 'estudiante', 'estudiante.institucion', 'docente', 'docente.institucion', 'tutor', 'tutor.estudiante', 'tutor.estudiante.usuario')
            ->search($search ?? '')->paginate(5);
        $roles = Rol::all();
        $instituciones = Institucion::all();
        $estudiantes = Estudiante::with('usuario', 'tutor')->get();
        $permisos = Permiso::all();

        return view('app.admin.users', compact('usuarioSesion', 'usuarios', 'roles', 'instituciones', 'estudiantes', 'permisos'));
    }

    public function adminStatistics()
    {
        $usuarioSesion = Auth::user()->load('rol');

        // Total de instituciones
        $totalInstituciones = Institucion::count();

        // Usuarios administrativos por institución
        $administrativosPorInstitucion = Institucion::withCount('administrativos')
            ->get()
            ->map(function ($institucion) {
                return [
                    'nombre' => $institucion->institucion_nombre,
                    'total' => $institucion->administrativos_count
                ];
            });

        // Docentes por institución
        $docentesPorInstitucion = Institucion::withCount('docentes')
            ->get()
            ->map(function ($institucion) {
                return [
                    'nombre' => $institucion->institucion_nombre,
                    'total' => $institucion->docentes_count
                ];
            });

        // Estudiantes por institución
        $estudiantesPorInstitucion = Institucion::withCount('estudiantes')
            ->get()
            ->map(function ($institucion) {
                return [
                    'nombre' => $institucion->institucion_nombre,
                    'total' => $institucion->estudiantes_count
                ];
            });

        // Total de grupos académicos
        $totalGrupos = Grupo::count();

        // Total de materias
        $totalMaterias = Materia::count();

        // Periodos académicos activos e inactivos
        $periodosActivos = PeriodoAcademico::where('periodo_academico_fin', '>=', now())->count();
        $periodosInactivos = PeriodoAcademico::where('periodo_academico_fin', '<', now())->count();

        // Tasa de crecimiento mensual
        $mesActual = now()->startOfMonth();
        $mesAnterior = now()->subMonth()->startOfMonth();

        $nuevasInstituciones = Institucion::where('created_at', '>=', $mesActual)->count();
        $nuevosEstudiantes = Estudiante::where('created_at', '>=', $mesActual)->count();
        $nuevosDocentes = Docente::where('created_at', '>=', $mesActual)->count();

        // Datos para gráficos de tendencia
        $tendencias = [
            'instituciones' => $this->getTendenciaMensual(Institucion::class),
            'estudiantes' => $this->getTendenciaMensual(Estudiante::class),
            'docentes' => $this->getTendenciaMensual(Docente::class)
        ];

        return view('app.admin.statistics', compact(
            'usuarioSesion',
            'totalInstituciones',
            'administrativosPorInstitucion',
            'docentesPorInstitucion',
            'estudiantesPorInstitucion',
            'totalGrupos',
            'totalMaterias',
            'periodosActivos',
            'periodosInactivos',
            'nuevasInstituciones',
            'nuevosEstudiantes',
            'nuevosDocentes',
            'tendencias'
        ));
    }

    private function getTendenciaMensual($model)
    {
        $tendencia = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $tendencia[] = [
                'mes' => $mes->format('M Y'),
                'total' => $model::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)
                    ->count()
            ];
        }
        return $tendencia;
    }

    // Administrative Views
    public function institution()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');

        $institucion = Institucion::where('institucion_id', $usuarioSesion->administrativo->institucion_id)->first();

        return view('app.administrative.institution', compact('usuarioSesion', 'institucion'));
    }

    public function institutionStatistics()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;
        $institucion = Institucion::findOrFail($institucion_id);

        // Totales básicos
        $totalEstudiantes = Estudiante::where('institucion_id', $institucion_id)
            ->whereHas('matriculas', function ($query) {
                $query->where('matricula_año', date('Y'));
            })
            ->count();

        $totalDocentes = Docente::where('institucion_id', $institucion_id)->count();
        $totalAdministrativos = Usuario::where('rol_id', 2)
            ->whereHas('administrativo', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id);
            })
            ->count();

        $totalMaterias = Materia::where('institucion_id', $institucion_id)->count();
        $totalGrupos = Grupo::where('institucion_id', $institucion_id)
            ->where('grupo_año', date('Y'))
            ->count();

        // Calendario académico
        $periodos = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->where('periodo_academico_año', date('Y'))
            ->orderBy('periodo_academico_inicio')
            ->get();

        $fechaInicio = $periodos->first()?->periodo_academico_inicio;
        $fechaFin = $periodos->last()?->periodo_academico_fin;
        $fechaActual = now();

        $diasTotales = $fechaInicio && $fechaFin ? Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) : 0;
        $diasTranscurridos = $fechaInicio ? Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaActual)) : 0;
        $porcentajeAvance = $diasTotales > 0 ? round(($diasTranscurridos / $diasTotales) * 100) : 0;

        // Estadísticas de asistencia
        $inasistenciasPorGrupo = Grupo::where('institucion_id', $institucion_id)
            ->where('grupo_año', date('Y'))
            ->withCount(['matriculas' => function ($query) {
                $query->whereHas('asistencias', function ($q) {
                    $q->where('asistencia_estado', 'ausente');
                });
            }])
            ->get()
            ->map(function ($grupo) {
                return [
                    'nombre' => $grupo->grupo_nombre,
                    'inasistencias' => $grupo->matriculas_count
                ];
            });

        // Inasistencias por día
        $inasistenciasPorDia = Asistencia::whereHas('matricula.estudiante', function ($query) use ($institucion_id) {
            $query->where('institucion_id', $institucion_id);
        })
            ->where('asistencia_estado', 'ausente')
            ->selectRaw('DATE(asistencia_fecha) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'inasistencias' => $item->total
                ];
            });

        // Estadísticas de rendimiento
        $rendimientoPorPeriodo = [];
        foreach ($periodos as $periodo) {
            $notas = Nota::whereHas('matricula', function ($query) use ($institucion_id) {
                $query->whereHas('estudiante', function ($q) use ($institucion_id) {
                    $q->where('institucion_id', $institucion_id);
                });
            })
                ->where('periodo_academico_id', $periodo->periodo_academico_id)
                ->get();

            $totalNotas = $notas->count();
            $aprobados = $notas->where('nota_valor', '>=', 3.0)->count();
            $reprobados = $totalNotas - $aprobados;

            $rendimientoPorPeriodo[] = [
                'periodo' => $periodo->periodo_academico_nombre,
                'aprobados' => $totalNotas > 0 ? round(($aprobados / $totalNotas) * 100) : 0,
                'reprobados' => $totalNotas > 0 ? round(($reprobados / $totalNotas) * 100) : 0
            ];
        }

        $promediosPorGrupo = Grupo::where('institucion_id', $institucion_id)
            ->where('grupo_año', date('Y'))
            ->with(['matriculas' => function ($query) {
                $query->with('notas');
            }])
            ->get()
            ->map(function ($grupo) {
                $promedio = $grupo->matriculas->flatMap->notas->avg('nota_valor');
                return [
                    'nombre' => $grupo->grupo_nombre,
                    'promedio' => round($promedio, 2)
                ];
            });

        $promediosPorMateria = Materia::where('institucion_id', $institucion_id)
            ->with(['asignaciones' => function ($query) {
                $query->with('notas');
            }])
            ->get()
            ->map(function ($materia) {
                $promedio = $materia->asignaciones->flatMap->notas->avg('nota_valor');
                return [
                    'nombre' => $materia->materia_nombre,
                    'promedio' => round($promedio, 2)
                ];
            })
            ->sortByDesc('promedio');

        return view('app.administrative.statistics', compact(
            'usuarioSesion',
            'institucion',
            'totalEstudiantes',
            'totalDocentes',
            'totalAdministrativos',
            'totalMaterias',
            'totalGrupos',
            'porcentajeAvance',
            'inasistenciasPorGrupo',
            'inasistenciasPorDia',
            'rendimientoPorPeriodo',
            'promediosPorGrupo',
            'promediosPorMateria'
        ));
    }

    public function administratives()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $search = request('search');

        $administrativos = Usuario::with('administrativo', 'administrativo.permisos', 'administrativo.institucion', 'rol')
            ->search($search ?? '')
            ->where('rol_id', 2)->whereNot('usuario_id', '=', $usuarioSesion->usuario_id)
            ->whereHas('administrativo', function ($query) use ($usuarioSesion) {
                $query->where('institucion_id', $usuarioSesion->administrativo->institucion_id);
            })
            ->paginate(5);

        $permisos = Permiso::all();
        $institucion = Institucion::where('institucion_id', $usuarioSesion->administrativo->institucion_id)->first();

        return view('app.administrative.administratives', compact('usuarioSesion', 'administrativos', 'permisos', 'institucion'));
    }

    public function teachers()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $search = request('search');

        $docentes = Usuario::with('docente', 'rol')
            ->search($search ?? '')
            ->where('rol_id', 3)
            ->whereHas('docente', function ($query) use ($usuarioSesion) {
                $query->where('institucion_id', $usuarioSesion->administrativo->institucion_id);
            })
            ->paginate(5);

        $institucion = Institucion::where('institucion_id', $usuarioSesion->administrativo->institucion_id)->first();


        return view('app.administrative.teachers', compact('usuarioSesion', 'docentes', 'institucion'));
    }

    public function students()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos', 'administrativo.institucion');
        $search = request('search');

        $estudiantes = Estudiante::with('usuario', 'tutor', 'tutor.usuario', 'matriculas', 'matriculas.grupo')
            ->where('institucion_id', $usuarioSesion->administrativo->institucion_id)
            ->whereHas('usuario', function ($query) use ($search) {
                $query->where('usuario_nombre', 'like', '%' . $search . '%')
                    ->orWhere('usuario_apellido', 'like', '%' . $search . '%')
                    ->orWhere('usuario_documento', 'like', '%' . $search . '%')
                    ->orWhere('usuario_correo', 'like', '%' . $search . '%');
            })
            ->orWhereHas('tutor.usuario', function ($query) use ($search) {
                $query->where('usuario_nombre', 'like', '%' . $search . '%')
                    ->orWhere('usuario_apellido', 'like', '%' . $search . '%')
                    ->orWhere('usuario_documento', 'like', '%' . $search . '%')
                    ->orWhere('usuario_correo', 'like', '%' . $search . '%');
            })
            ->orWhereHas('matriculas.grupo', function ($query) use ($search) {
                $query->where('grupo_nombre', 'like', '%' . $search . '%');
            })
            ->paginate(5);

        $grupos = Grupo::with('grado', 'grado.nivel')
            ->where('institucion_id', $usuarioSesion->administrativo->institucion_id)
            ->get();

        return view('app.administrative.students', compact('usuarioSesion', 'estudiantes', 'grupos'));
    }

    public function enrollmentsRequests()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $institucion = Institucion::findOrFail($institucion_id);

        $solicitudes = SolicitudMatricula::with('estudiante', 'estudianteNuevo', 'tutorNuevo', 'grado', 'grado.grupos', 'grado.grupos.matriculas', 'institucion')
            ->where('institucion_id', $institucion_id)
            ->where('solicitud_estado', 'pendiente')
            ->whereHas('grado.grupos', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id)
                    ->where('grupo_año', date('Y'));
            })
            ->paginate(10);

        $grupos = Grupo::with('grado', 'grado.nivel')
            ->where('institucion_id', $institucion_id)
            ->get();

        return view('app.administrative.requests', compact('usuarioSesion', 'solicitudes', 'grupos', 'institucion'));
    }

    public function periods()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $availableYears = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->select('periodo_academico_año as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $selectedYear = request('year_filter');

        $query = PeriodoAcademico::where('institucion_id', $institucion_id);

        if ($selectedYear) {
            $query->where('periodo_academico_año', $selectedYear);
        }

        $periodos = $query->orderBy('periodo_academico_año', 'desc')
            ->orderBy('periodo_academico_inicio', 'asc')
            ->paginate(8);

        return view('app.administrative.periods', compact('usuarioSesion', 'periodos', 'availableYears', 'selectedYear'));
    }

    public function groups()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $grupos = Grupo::with('grado', 'grado.nivel', 'asignaciones')
            ->where('institucion_id', $institucion_id);

        $materias = Materia::where('institucion_id', $institucion_id)->get();

        $docentes = Docente::where('institucion_id', $institucion_id)->get();

        $availableYears = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->select('periodo_academico_año as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $selectedYear = request('year_filter');

        if ($selectedYear) {
            $grupos = $grupos->where('grupo_año', $selectedYear);
        }

        $grados = Grado::with('nivel')->get();
        $grupos = $grupos->paginate(10);

        return view('app.administrative.groups', compact('usuarioSesion', 'grupos', 'grados', 'materias', 'docentes', 'availableYears', 'selectedYear'));
    }

    public function subjects()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $materias = Materia::where('institucion_id', $institucion_id)->paginate(10);

        return view('app.administrative.subjects', compact('usuarioSesion', 'materias'));
    }

    public function schedules()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $bloques = Bloque::where('institucion_id', $institucion_id)
            ->orderByRaw("FIELD(bloque_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') ASC")
            ->get();
        $grupos = Grupo::where('institucion_id', $institucion_id)->get();

        // Obtener el horario del grupo seleccionado
        $selectedGroupId = request('grupo_id');
        $bloquesHorario = Bloque::where('institucion_id', $institucion_id)
            ->orderByRaw("FIELD(bloque_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') ASC")
            ->get()
            ->groupBy('bloque_dia');
        $horarios = Horario::with('bloque', 'asignacion', 'asignacion.grupo', 'asignacion.materia', 'asignacion.docente')
            ->whereHas('asignacion.grupo', function ($query) use ($selectedGroupId) {
                $query->where('grupo_id', $selectedGroupId);
            })->get();
        $asignaciones = Asignacion::with('materia', 'docente', 'docente.usuario')->where('grupo_id', $selectedGroupId)->get();

        return view('app.administrative.schedules', compact('usuarioSesion', 'bloques', 'grupos', 'horarios', 'asignaciones', 'bloquesHorario', 'selectedGroupId'));
    }

    public function absences()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;
        $justificationFilter = request('justification_filter');

        $inasistencias = Asistencia::with('matricula', 'matricula.estudiante', 'matricula.estudiante.usuario')
            ->where('asistencia_estado', 'ausente')
            ->whereHas('matricula', function ($query) use ($institucion_id) {
                $query->where('matricula_año', date('Y'));
            })
            ->whereHas('matricula.estudiante', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id);
            })
            ->orderBy('asistencia_fecha', 'desc');

        if ($justificationFilter == 'justificada') {
            $inasistencias = $inasistencias->where('asistencia_motivo', '!=', 'null');
        } elseif ($justificationFilter == 'injustificada') {
            $inasistencias = $inasistencias->where('asistencia_motivo', 'null');
        }

        $inasistencias = $inasistencias->paginate(20);

        return view('app.administrative.absences', compact('usuarioSesion', 'inasistencias', 'justificationFilter'));
    }

    public function observations()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;

        $observaciones = Observacion::with('matricula', 'matricula.estudiante', 'matricula.estudiante.usuario')
            ->whereHas('matricula.estudiante', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id);
            })
            ->orderBy('observacion_fecha', 'desc')
            ->paginate(20);

        return view('app.administrative.observations', compact('usuarioSesion', 'observaciones'));
    }

    public function payments()
    {
        $usuarioSesion = Auth::user()->load('rol', 'administrativo', 'administrativo.permisos');
        $institucion_id = $usuarioSesion->administrativo->institucion_id;
        $search = request('search');

        $conceptos = ConceptoPago::where('institucion_id', $institucion_id)->get();
        $estudiantes = Estudiante::with('usuario', 'matriculas', 'matriculas.pagos')
            ->where('institucion_id', $institucion_id)
            ->whereHas('matriculas', function ($query) {
                $query->where('matricula_año', date('Y'));
            })
            ->whereHas('usuario', function ($query) use ($search) {
                $query->where('usuario_nombre', 'like', '%' . $search . '%')
                    ->orWhere('usuario_apellido', 'like', '%' . $search . '%')
                    ->orWhere('usuario_documento', 'like', '%' . $search . '%')
                    ->orWhere('usuario_correo', 'like', '%' . $search . '%');
            })
            ->paginate(10);

        return view('app.administrative.payments', compact('usuarioSesion', 'estudiantes', 'conceptos'));
    }

    public function teacherSchedule()
    {
        $usuarioSesion = Auth::user()->load('rol', 'docente');
        $institucion_id = $usuarioSesion->docente->institucion_id;

        $horarios = Horario::with('bloque', 'asignacion', 'asignacion.grupo')
            ->whereHas('asignacion', function ($query) use ($usuarioSesion) {
                $query->where('docente_id', $usuarioSesion->docente->docente_id);
            })
            ->whereHas('asignacion.grupo', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id)->where('grupo_año', date('Y'));
            })
            ->get();

        $bloquesHorario = Bloque::where('institucion_id', $institucion_id)
            ->orderByRaw("FIELD(bloque_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') ASC")
            ->get()
            ->groupBy('bloque_dia');

        $asignaciones = Asignacion::with('grupo', 'materia', 'horarios', 'horarios.bloque')
            ->where('docente_id', $usuarioSesion->docente->docente_id)
            ->whereHas('grupo', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id)->where('grupo_año', date('Y'));
            })
            ->get();

        return view('app.teacher.schedule', compact('usuarioSesion', 'horarios', 'asignaciones', 'bloquesHorario'));
    }

    public function teacherCourses()
    {
        $usuarioSesion = Auth::user()->load('rol', 'docente');
        $institucion_id = $usuarioSesion->docente->institucion_id;


        $asignaciones = Asignacion::with('grupo', 'materia', 'horarios', 'horarios.bloque')
            ->where('docente_id', $usuarioSesion->docente->docente_id)
            ->whereHas('grupo', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id)->where('grupo_año', date('Y'));
            })
            ->get();

        return view('app.teacher.courses', compact('usuarioSesion', 'asignaciones'));
    }

    public function teacherCourseDetails($asignacion_id)
    {
        $usuarioSesion = Auth::user()->load('rol', 'docente');
        $institucion_id = $usuarioSesion->docente->institucion_id;

        $institucion = Institucion::findOrFail($institucion_id);

        $asignacion = Asignacion::with('grupo', 'materia')
            ->where('asignacion_id', $asignacion_id)
            ->where('docente_id', $usuarioSesion->docente->docente_id)
            ->whereHas('grupo', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id)->where('grupo_año', date('Y'));
            })
            ->firstOrFail();

        $estudiantes = Estudiante::with('usuario', 'matriculas', 'tutor', 'tutor.usuario')
            ->where('institucion_id', $institucion_id)
            ->whereHas('matriculas', function ($query) use ($asignacion) {
                $query->where('grupo_id', $asignacion->grupo->grupo_id);
            })
            ->get()
            ->sortBy(function ($estudiante) {
                return $estudiante->usuario->usuario_apellido;
            });

        $observaciones = Observacion::with('matricula', 'matricula.estudiante', 'matricula.estudiante.usuario')
            ->whereHas('matricula.estudiante', function ($query) use ($institucion_id) {
                $query->where('institucion_id', $institucion_id);
            })
            ->whereHas('matricula', function ($query) use ($asignacion) {
                $query->where('grupo_id', $asignacion->grupo->grupo_id);
            })
            ->orderBy('observacion_fecha', 'desc')
            ->get();

        $periodos = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->where('periodo_academico_año', date('Y'))
            ->orderBy('periodo_academico_inicio', 'asc')
            ->orderBy('periodo_academico_fin', 'asc')
            ->get();

        $asistencia_fecha = request('asistencia_fecha');
        $asistencias = Asistencia::with('matricula')
            ->where('asistencia_fecha',  $asistencia_fecha)
            ->whereHas('matricula', function ($query) use ($asignacion) {
                $query->where('grupo_id', $asignacion->grupo->grupo_id);
            })
            ->get();

        $notas = Nota::whereHas('matricula', function ($query) use ($asignacion) {
            $query->where('grupo_id', $asignacion->grupo->grupo_id);
        })
            ->whereHas('asignacion', function ($query) use ($asignacion) {
                $query->where('asignacion_id', $asignacion->asignacion_id);
            })
            ->get();
        $periodos = PeriodoAcademico::where('institucion_id', $institucion_id)->where('periodo_academico_año', date('Y'))->orderBy('periodo_academico_inicio', 'asc')->get();

        return view('app.teacher.course', compact('usuarioSesion', 'asignacion', 'estudiantes', 'observaciones', 'periodos', 'asistencias', 'institucion', 'notas'));
    }

    public function studentSchedule()
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');
        $institucion_id = $usuarioSesion->estudiante->institucion_id;

        $horarios = Horario::with('bloque', 'asignacion', 'asignacion.docente', 'asignacion.docente.usuario', 'asignacion.grupo')
            ->whereHas('asignacion', function ($query) use ($usuarioSesion) {
                $query->where('grupo_id', $usuarioSesion->estudiante->matriculas->first()->grupo_id);
            })
            ->get();
        $bloquesHorario = Bloque::where('institucion_id', $institucion_id)
            ->orderByRaw("FIELD(bloque_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') ASC")
            ->get()
            ->groupBy('bloque_dia');

        return view('app.student.schedule', compact('usuarioSesion', 'horarios', 'bloquesHorario'));
    }

    public function studentSubjects()
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');
        $institucion_id = $usuarioSesion->estudiante->institucion_id;
        $grupo_id = $usuarioSesion->estudiante->matriculas->first()->grupo_id;

        $asignaciones = Asignacion::with(['materia', 'docente.usuario'])
            ->where('grupo_id', $grupo_id)
            ->get();

        return view('app.student.subjects', compact('usuarioSesion', 'asignaciones'));
    }

    public function studentSubjectDetails($asignacion_id)
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');
        $institucion_id = $usuarioSesion->estudiante->institucion_id;
        $matricula_id = $usuarioSesion->estudiante->matriculas->last()->matricula_id;

        $asignacion = Asignacion::with(['materia', 'docente.usuario', 'grupo'])
            ->findOrFail($asignacion_id);

        $periodos = PeriodoAcademico::with('notas')
            ->where('institucion_id', $institucion_id)
            ->where('periodo_academico_año', date('Y'))
            ->orderBy('periodo_academico_inicio')
            ->get();

        $asistencias = Asistencia::where('matricula_id', $matricula_id)
            ->orderBy('asistencia_fecha', 'desc')
            ->get();

        $notas = Nota::where('matricula_id', $matricula_id)
            ->where('asignacion_id', $asignacion_id)
            ->get();

        $institucion = Institucion::findOrFail($institucion_id);

        return view('app.student.subject', compact('usuarioSesion', 'asignacion', 'periodos', 'asistencias', 'notas', 'institucion'));
    }

    public function studentAbsences()
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');
        $matricula_id = $usuarioSesion->estudiante->matriculas->last()->matricula_id;

        $asistencias = Asistencia::where('matricula_id', $matricula_id)
            ->where('asistencia_estado', ['ausente', 'retardo'])
            ->orderBy('asistencia_fecha', 'desc')
            ->get();

        return view('app.student.absences', compact('usuarioSesion', 'asistencias'));
    }

    public function studentObservations()
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');
        $matricula_id = $usuarioSesion->estudiante->matriculas->last()->matricula_id;

        $observaciones = Observacion::where('matricula_id', $matricula_id)
            ->orderBy('observacion_fecha', 'desc')
            ->get();

        return view('app.student.observations', compact('usuarioSesion', 'observaciones'));
    }

    public function tutorStudentProfile()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.matriculas', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;

        $estudiante = Estudiante::with(
            'usuario',
            'matriculas',
            'matriculas.grupo',
            'matriculas.grupo.asignaciones',
            'matriculas.grupo.asignaciones.materia',
            'matriculas.grupo.asignaciones.docente',
            'matriculas.grupo.asignaciones.docente.usuario',
            'matriculas.observaciones'
        )
            ->where('institucion_id', $institucion_id)
            ->where('estudiante_id', $usuarioSesion->tutor->estudiante_id)
            ->first();

        $periodo = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->where('periodo_academico_año', date('Y'))
            ->orderBy('periodo_academico_inicio', 'asc')
            ->orderBy('periodo_academico_fin', 'asc')
            ->first();

        $promedio = Nota::where('matricula_id', $usuarioSesion->tutor->estudiante->matriculas->last()->matricula_id)
            ->get()
            ->avg('nota_valor');

        return view('app.tutor.student', compact('usuarioSesion', 'estudiante', 'periodo', 'promedio'));
    }

    public function tutorGrades()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.matriculas', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;
        $matricula_id = $usuarioSesion->tutor->estudiante->matriculas->last()->matricula_id;

        $institucion = Institucion::findOrFail($institucion_id);

        $asignaciones = Asignacion::with('notas', 'materia')
            ->where('grupo_id', $usuarioSesion->tutor->estudiante->matriculas->first()->grupo_id)
            ->get();

        $periodos = PeriodoAcademico::where('institucion_id', $institucion_id)->where('periodo_academico_año', date('Y'))->orderBy('periodo_academico_inicio', 'asc')->get();
        $notas = Nota::where('matricula_id', $matricula_id)->get();

        return view('app.tutor.grades', compact('usuarioSesion', 'asignaciones', 'institucion', 'notas', 'periodos'));
    }

    public function tutorGradesExport()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.matriculas', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;
        $matricula_id = $usuarioSesion->tutor->estudiante->matriculas->last()->matricula_id;

        $estudiante = $usuarioSesion->tutor->estudiante;

        $institucion = Institucion::findOrFail($institucion_id);

        $asignaciones = Asignacion::with('notas', 'materia')
            ->where('grupo_id', $usuarioSesion->tutor->estudiante->matriculas->first()->grupo_id)
            ->get();

        $notas = Nota::where('matricula_id', $matricula_id)->get();
        $periodos = PeriodoAcademico::where('institucion_id', $institucion_id)->where('periodo_academico_año', date('Y'))->orderBy('periodo_academico_inicio', 'asc')->get();

        $data = [
            'estudiante' => $estudiante,
            'asignaciones' => $asignaciones,
            'promedio_general' => $notas->avg('nota_valor'),
            'notas' => $notas,
            'periodos' => $periodos,
            'institucion' => $institucion
        ];

        $pdf = PDF::loadView('exports.grades-pdf', $data);
        return $pdf->download('boletin_' . $estudiante->usuario->usuario_documento . '.pdf');
    }

    public function tutorSchedule()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.matriculas', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;

        $bloquesHorario = Bloque::where('institucion_id', $institucion_id)
            ->orderByRaw("FIELD(bloque_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') ASC")
            ->get()
            ->groupBy('bloque_dia');

        $horarios = Horario::with('bloque', 'asignacion', 'asignacion.docente', 'asignacion.docente.usuario', 'asignacion.grupo')
            ->whereHas('asignacion', function ($query) use ($usuarioSesion) {
                $query->where('grupo_id', $usuarioSesion->tutor->estudiante->matriculas->first()->grupo_id);
            })
            ->get();

        return view('app.tutor.schedule', compact('usuarioSesion', 'bloquesHorario', 'horarios'));
    }

    public function tutorAbsences()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.matriculas', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;

        $asistencias = Asistencia::with('matricula', 'matricula.estudiante', 'matricula.estudiante.usuario')
            ->where('matricula_id', $usuarioSesion->tutor->estudiante->matriculas->last()->matricula_id)
            ->where('asistencia_estado', ['ausente', 'retardo'])
            ->orderBy('asistencia_fecha', 'desc')
            ->get();

        return view('app.tutor.absences', compact('usuarioSesion', 'asistencias'));
    }

    public function tutorObservations()
    {
        $usuarioSesion = Auth::user()->load('rol', 'tutor', 'tutor.estudiante', 'tutor.estudiante.usuario');
        $institucion_id = $usuarioSesion->tutor->estudiante->institucion_id;
        $matricula_id = $usuarioSesion->tutor->estudiante->matriculas->last()->matricula_id;

        $observaciones = Observacion::with('matricula', 'matricula.estudiante', 'matricula.estudiante.usuario')
            ->where('matricula_id', $matricula_id)
            ->orderBy('observacion_fecha', 'desc')
            ->get();

        return view('app.tutor.observations', compact('usuarioSesion', 'observaciones'));
    }

    public function studentProfile($id)
    {
        $usuarioSesion = Auth::user()->load('rol', 'estudiante', 'estudiante.matriculas', 'estudiante.matriculas.grupo');

        $estudiante = Estudiante::with(
            'usuario',
            'matriculas',
            'matriculas.grupo',
            'matriculas.grupo.asignaciones',
            'matriculas.grupo.asignaciones.materia',
            'matriculas.grupo.asignaciones.docente',
            'matriculas.grupo.asignaciones.docente.usuario',
            'matriculas.observaciones'
        )
            ->where('estudiante_id', $id)
            ->first();

        $institucion_id = $estudiante->institucion_id;

        $periodo = PeriodoAcademico::where('institucion_id', $institucion_id)
            ->where('periodo_academico_año', date('Y'))
            ->orderBy('periodo_academico_inicio', 'asc')
            ->orderBy('periodo_academico_fin', 'asc')
            ->first();

        $promedio = Nota::where('matricula_id', $estudiante->matriculas->last()->matricula_id)
            ->get()
            ->avg('nota_valor');

        return view('app.student-profile', compact('usuarioSesion', 'estudiante', 'periodo', 'promedio'));
    }
}
