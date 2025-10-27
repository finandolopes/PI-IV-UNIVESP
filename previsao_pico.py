import mysql.connector
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error
import matplotlib.pyplot as plt
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

def conectar_bd():
    """Conecta ao banco de dados MySQL"""
    try:
        conn = mysql.connector.connect(**db_config)
        return conn
    except mysql.connector.Error as err:
        print(f"Erro ao conectar ao banco de dados: {err}")
        return None

def carregar_dados_treinamento():
    """Carrega dados históricos para treinamento do modelo"""
    conn = conectar_bd()
    if conn:
        query = """
        SELECT
            DATE(data_visita) as data,
            HOUR(data_visita) as hora,
            DAYOFWEEK(data_visita) as dia_semana,
            MONTH(data_visita) as mes,
            COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        GROUP BY DATE(data_visita), HOUR(data_visita)
        ORDER BY data_visita
        """
        df = pd.read_sql(query, conn)
        conn.close()
        return df
    return None

def preparar_features(df):
    """Prepara as features para o modelo de machine learning"""
    # Criar features adicionais
    df['hora_sin'] = np.sin(2 * np.pi * df['hora'] / 24)
    df['hora_cos'] = np.cos(2 * np.pi * df['hora'] / 24)
    df['dia_semana_sin'] = np.sin(2 * np.pi * df['dia_semana'] / 7)
    df['dia_semana_cos'] = np.cos(2 * np.pi * df['dia_semana'] / 7)

    # Features para o modelo
    features = ['hora', 'dia_semana', 'mes', 'hora_sin', 'hora_cos', 'dia_semana_sin', 'dia_semana_cos']
    X = df[features]
    y = df['visitas']

    return X, y

def treinar_modelo(X, y):
    """Treina o modelo de Random Forest"""
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    modelo = RandomForestRegressor(
        n_estimators=100,
        random_state=42,
        max_depth=10
    )

    modelo.fit(X_train, y_train)

    # Avaliar o modelo
    y_pred = modelo.predict(X_test)
    mae = mean_absolute_error(y_test, y_pred)
    rmse = np.sqrt(mean_squared_error(y_test, y_pred))

    print(f"MAE (Mean Absolute Error): {mae:.2f}")
    print(f"RMSE (Root Mean Squared Error): {rmse:.2f}")

    return modelo

def gerar_previsoes(modelo, dias_futuros=7):
    """Gera previsões para os próximos dias"""
    previsoes = []

    data_atual = datetime.now()

    for dia in range(dias_futuros):
        for hora in range(24):
            data_previsao = data_atual + timedelta(days=dia)
            dia_semana = data_previsao.weekday() + 1  # 1-7 (segunda a domingo)
            mes = data_previsao.month

            # Preparar features
            hora_sin = np.sin(2 * np.pi * hora / 24)
            hora_cos = np.cos(2 * np.pi * hora / 24)
            dia_semana_sin = np.sin(2 * np.pi * dia_semana / 7)
            dia_semana_cos = np.cos(2 * np.pi * dia_semana / 7)

            features = np.array([[hora, dia_semana, mes, hora_sin, hora_cos, dia_semana_sin, dia_semana_cos]])
            previsao = modelo.predict(features)[0]

            previsoes.append({
                'data': data_previsao.date(),
                'hora': hora,
                'previsao_visitas': round(previsao),
                'dia_semana': dia_semana
            })

    return pd.DataFrame(previsoes)

def salvar_previsoes_bd(df_previsoes):
    """Salva as previsões no banco de dados"""
    conn = conectar_bd()
    if conn:
        cursor = conn.cursor()

        # Limpar previsões antigas
        cursor.execute("DELETE FROM previsoes_pico")

        # Inserir novas previsões
        for _, row in df_previsoes.iterrows():
            sql = """
            INSERT INTO previsoes_pico (data_previsao, hora_previsao, previsao_visitas, modelo_usado)
            VALUES (%s, %s, %s, %s)
            """
            hora_str = f"{int(row['hora']):02d}:00:00"
            cursor.execute(sql, (row['data'], hora_str, row['previsao_visitas'], 'RandomForest'))

        conn.commit()
        cursor.close()
        conn.close()
        print("Previsões salvas no banco de dados!")
    else:
        print("Erro ao salvar previsões no banco de dados.")

def plotar_previsoes(df_previsoes):
    """Plota as previsões de visitas"""
    # Agrupar por hora do dia (média dos próximos dias)
    previsoes_por_hora = df_previsoes.groupby('hora')['previsao_visitas'].mean()

    plt.figure(figsize=(12, 6))
    previsoes_por_hora.plot(kind='line', marker='o')
    plt.title('Previsão de Visitas por Hora do Dia')
    plt.xlabel('Hora')
    plt.ylabel('Visitas Previstas')
    plt.grid(True)
    plt.tight_layout()
    plt.savefig('previsao_pico.png')
    plt.show()

def main():
    """Função principal"""
    print("Iniciando treinamento do modelo de previsão de horários de pico...")

    # Carregar dados
    df = carregar_dados_treinamento()
    if df is None or df.empty:
        print("Nenhum dado encontrado para treinamento.")
        return

    print(f"Dados carregados: {len(df)} registros")

    # Preparar features
    X, y = preparar_features(df)

    # Treinar modelo
    modelo = treinar_modelo(X, y)

    # Gerar previsões
    df_previsoes = gerar_previsoes(modelo)
    print(f"Previsões geradas para {len(df_previsoes)} horários")

    # Salvar no banco
    salvar_previsoes_bd(df_previsoes)

    # Plotar resultados
    plotar_previsoes(df_previsoes)

    print("Modelo de previsão concluído!")

if __name__ == "__main__":
    main()
