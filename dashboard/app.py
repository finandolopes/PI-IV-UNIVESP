import dash
from dash import html, dcc, Input, Output
import plotly.express as px
import plotly.graph_objects as go
import pandas as pd
import mysql.connector
from datetime import datetime, timedelta
import warnings
warnings.filterwarnings('ignore')

# Configurações de conexão com o banco de dados
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'confinter'
}

# Inicializar o app Dash
app = dash.Dash(__name__, title='CONFINTER - Dashboard de Análise')

# Layout do dashboard
app.layout = html.Div([
    html.H1('CONFINTER - Dashboard de Análise de Dados', style={'textAlign': 'center', 'color': '#2c3e50'}),

    # Filtros
    html.Div([
        html.Div([
            html.Label('Período:'),
            dcc.DatePickerRange(
                id='date-picker',
                start_date=datetime.now() - timedelta(days=30),
                end_date=datetime.now(),
                display_format='DD/MM/YYYY'
            )
        ], style={'margin': '10px'}),

        html.Div([
            html.Label('Tipo de Análise:'),
            dcc.Dropdown(
                id='analysis-type',
                options=[
                    {'label': 'Visitas', 'value': 'visitas'},
                    {'label': 'Requisições', 'value': 'requisicoes'},
                    {'label': 'Horários de Pico', 'value': 'picos'}
                ],
                value='visitas',
                style={'width': '200px'}
            )
        ], style={'margin': '10px'})
    ], style={'display': 'flex', 'justifyContent': 'center', 'flexWrap': 'wrap'}),

    # Gráficos
    html.Div([
        dcc.Graph(id='main-graph', style={'height': '500px'}),
        dcc.Graph(id='secondary-graph', style={'height': '500px'})
    ], style={'display': 'flex', 'flexWrap': 'wrap'}),

    # Métricas
    html.Div([
        html.Div([
            html.H3('Total de Visitas'),
            html.P(id='total-visitas', style={'fontSize': '24px', 'fontWeight': 'bold'})
        ], className='metric-card'),

        html.Div([
            html.H3('Total de Requisições'),
            html.P(id='total-requisicoes', style={'fontSize': '24px', 'fontWeight': 'bold'})
        ], className='metric-card'),

        html.Div([
            html.H3('Taxa de Conversão'),
            html.P(id='taxa-conversao', style={'fontSize': '24px', 'fontWeight': 'bold'})
        ], className='metric-card')
    ], style={'display': 'flex', 'justifyContent': 'space-around', 'marginTop': '20px'}),

    # Tabela de dados recentes
    html.Div([
        html.H3('Dados Recentes'),
        html.Div(id='recent-data-table')
    ], style={'marginTop': '40px'}),

    # Intervalo para atualização automática
    dcc.Interval(
        id='interval-component',
        interval=5*60*1000,  # 5 minutos
        n_intervals=0
    )
], style={'fontFamily': 'Arial, sans-serif', 'padding': '20px'})

def conectar_bd():
    """Conecta ao banco de dados MySQL"""
    try:
        conn = mysql.connector.connect(**db_config)
        return conn
    except mysql.connector.Error as err:
        print(f"Erro ao conectar ao banco de dados: {err}")
        return None

@app.callback(
    [Output('main-graph', 'figure'),
     Output('secondary-graph', 'figure'),
     Output('total-visitas', 'children'),
     Output('total-requisicoes', 'children'),
     Output('taxa-conversao', 'children'),
     Output('recent-data-table', 'children')],
    [Input('date-picker', 'start_date'),
     Input('date-picker', 'end_date'),
     Input('analysis-type', 'value'),
     Input('interval-component', 'n_intervals')]
)
def update_dashboard(start_date, end_date, analysis_type, n):
    """Atualiza os gráficos e métricas do dashboard"""

    # Carregar dados
    conn = conectar_bd()
    if not conn:
        return {}, {}, "Erro", "Erro", "Erro", html.P("Erro na conexão com o banco")

    # Query para visitas
    query_visitas = f"""
    SELECT DATE(data_visita) as data, COUNT(*) as visitas
    FROM contador_visitas
    WHERE data_visita BETWEEN '{start_date}' AND '{end_date}'
    GROUP BY DATE(data_visita)
    ORDER BY data
    """

    # Query para requisições
    query_requisicoes = f"""
    SELECT DATE(data_requisicao) as data, COUNT(*) as requisicoes
    FROM requisicoes
    WHERE data_requisicao BETWEEN '{start_date}' AND '{end_date}'
    GROUP BY DATE(data_requisicao)
    ORDER BY data
    """

    df_visitas = pd.read_sql(query_visitas, conn)
    df_requisicoes = pd.read_sql(query_requisicoes, conn)

    # Métricas
    total_visitas = df_visitas['visitas'].sum() if not df_visitas.empty else 0
    total_requisicoes = df_requisicoes['requisicoes'].sum() if not df_requisicoes.empty else 0
    taxa_conversao = f"{(total_requisicoes / total_visitas * 100):.2f}%" if total_visitas > 0 else "0%"

    # Gráfico principal
    if analysis_type == 'visitas':
        if not df_visitas.empty:
            fig_main = px.line(df_visitas, x='data', y='visitas', title='Visitas por Dia')
        else:
            fig_main = go.Figure()
            fig_main.add_annotation(text="Nenhum dado encontrado", showarrow=False)
    elif analysis_type == 'requisicoes':
        if not df_requisicoes.empty:
            fig_main = px.bar(df_requisicoes, x='data', y='requisicoes', title='Requisições por Dia')
        else:
            fig_main = go.Figure()
            fig_main.add_annotation(text="Nenhum dado encontrado", showarrow=False)
    else:
        # Análise de horários de pico
        query_pico = f"""
        SELECT HOUR(data_visita) as hora, COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita BETWEEN '{start_date}' AND '{end_date}'
        GROUP BY HOUR(data_visita)
        ORDER BY hora
        """
        df_pico = pd.read_sql(query_pico, conn)
        if not df_pico.empty:
            fig_main = px.bar(df_pico, x='hora', y='visitas', title='Visitas por Hora do Dia')
        else:
            fig_main = go.Figure()
            fig_main.add_annotation(text="Nenhum dado encontrado", showarrow=False)

    # Gráfico secundário - Heatmap de horários
    query_heatmap = f"""
    SELECT DAYOFWEEK(data_visita) as dia_semana, HOUR(data_visita) as hora, COUNT(*) as visitas
    FROM contador_visitas
    WHERE data_visita BETWEEN '{start_date}' AND '{end_date}'
    GROUP BY DAYOFWEEK(data_visita), HOUR(data_visita)
    """
    df_heatmap = pd.read_sql(query_heatmap, conn)
    if not df_heatmap.empty:
        pivot = df_heatmap.pivot(index='dia_semana', columns='hora', values='visitas').fillna(0)
        fig_secondary = px.imshow(pivot, title='Heatmap: Visitas por Dia da Semana e Hora')
    else:
        fig_secondary = go.Figure()
        fig_secondary.add_annotation(text="Nenhum dado encontrado", showarrow=False)

    # Tabela de dados recentes
    query_recent = """
    SELECT 'Visita' as tipo, data_visita as data, ip_address as info
    FROM contador_visitas
    ORDER BY data_visita DESC
    LIMIT 10
    UNION ALL
    SELECT 'Requisição' as tipo, data_hora as data, CONCAT(c.nome, ' - ', r.tipo) as info
    FROM requisicoes r
    LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
    ORDER BY data_hora DESC
    LIMIT 10
    """
    df_recent = pd.read_sql(query_recent, conn)
    table = html.Table([
        html.Thead(html.Tr([html.Th(col) for col in df_recent.columns])),
        html.Tbody([
            html.Tr([html.Td(df_recent.iloc[i][col]) for col in df_recent.columns])
            for i in range(len(df_recent))
        ])
    ])

    conn.close()

    return fig_main, fig_secondary, str(total_visitas), str(total_requisicoes), taxa_conversao, table

if __name__ == '__main__':
    app.run_server(debug=True, host='0.0.0.0', port=8050)
