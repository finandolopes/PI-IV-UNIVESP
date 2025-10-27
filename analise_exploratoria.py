import mysql.connector
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from datetime import datetime
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

def carregar_dados_visitas():
    """Carrega dados de visitas do banco"""
    conn = conectar_bd()
    if conn:
        query = """
        SELECT data_visita, ip_address, pagina, sessao_id
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY data_visita DESC
        """
        df = pd.read_sql(query, conn)
        conn.close()
        return df
    return None

def carregar_dados_requisicoes():
    """Carrega dados de requisições do banco"""
    conn = conectar_bd()
    if conn:
        query = """
        SELECT r.id_requisicao, r.horario_contato, r.data_requisicao, r.data_hora,
               r.tipo, r.categoria, r.outros_info,
               c.nome, c.email, c.telefone
        FROM requisicoes r
        LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
        WHERE r.data_requisicao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY r.data_hora DESC
        """
        df = pd.read_sql(query, conn)
        conn.close()
        return df
    return None

def analise_exploratoria_visitas(df_visitas):
    """Realiza análise exploratória dos dados de visitas"""
    print("=== ANÁLISE EXPLORATÓRIA - VISITAS ===")
    print(f"Total de visitas: {len(df_visitas)}")
    print(f"Período analisado: {df_visitas['data_visita'].min()} a {df_visitas['data_visita'].max()}")

    # Visitas por dia
    df_visitas['data'] = df_visitas['data_visita'].dt.date
    visitas_por_dia = df_visitas.groupby('data').size()

    plt.figure(figsize=(12, 6))
    visitas_por_dia.plot(kind='line', marker='o')
    plt.title('Visitas por Dia')
    plt.xlabel('Data')
    plt.ylabel('Número de Visitas')
    plt.xticks(rotation=45)
    plt.tight_layout()
    plt.savefig('visitas_por_dia.png')
    plt.show()

    # Visitas por hora
    df_visitas['hora'] = df_visitas['data_visita'].dt.hour
    visitas_por_hora = df_visitas.groupby('hora').size()

    plt.figure(figsize=(10, 6))
    visitas_por_hora.plot(kind='bar')
    plt.title('Visitas por Hora do Dia')
    plt.xlabel('Hora')
    plt.ylabel('Número de Visitas')
    plt.tight_layout()
    plt.savefig('visitas_por_hora.png')
    plt.show()

def analise_exploratoria_requisicoes(df_requisicoes):
    """Realiza análise exploratória dos dados de requisições"""
    print("\n=== ANÁLISE EXPLORATÓRIA - REQUISIÇÕES ===")
    print(f"Total de requisições: {len(df_requisicoes)}")

    # Taxa de preenchimento por categoria
    plt.figure(figsize=(8, 6))
    df_requisicoes['categoria'].value_counts().plot(kind='pie', autopct='%1.1f%%')
    plt.title('Distribuição de Requisições por Categoria')
    plt.tight_layout()
    plt.savefig('requisicoes_por_categoria.png')
    plt.show()

    # Horários preferidos para contato
    df_requisicoes['hora_contato'] = pd.to_datetime(df_requisicoes['horario_contato'], format='%H:%M:%S').dt.hour
    horarios = df_requisicoes.groupby('hora_contato').size()

    plt.figure(figsize=(10, 6))
    horarios.plot(kind='bar')
    plt.title('Horários Preferidos para Contato')
    plt.xlabel('Hora')
    plt.ylabel('Número de Requisições')
    plt.tight_layout()
    plt.savefig('horarios_contato.png')
    plt.show()

def main():
    """Função principal"""
    print("Iniciando análise exploratória dos dados...")

    # Carregar dados
    df_visitas = carregar_dados_visitas()
    df_requisicoes = carregar_dados_requisicoes()

    if df_visitas is not None and not df_visitas.empty:
        analise_exploratoria_visitas(df_visitas)
    else:
        print("Nenhum dado de visitas encontrado.")

    if df_requisicoes is not None and not df_requisicoes.empty:
        analise_exploratoria_requisicoes(df_requisicoes)
    else:
        print("Nenhum dado de requisições encontrado.")

    print("\nAnálise exploratória concluída!")

if __name__ == "__main__":
    main()
