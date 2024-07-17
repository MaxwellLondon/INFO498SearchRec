import pyterrier as pt
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import CountVectorizer

df = pd.read_csv("backend/updatedGameQueries.csv")

def search(query):
    # Get the query from the request parameters
    global df  # Assuming you want to use the global df variable
    
    if query not in df['query'].values:
        # Add a new query row using pd.concat() only if it's not a duplicate
        new_row = pd.DataFrame({'query': [query]})
        df = pd.concat([df, new_row], ignore_index=True)

    rec_dict = recommendations(query)
    # Perform the search using PyTerrier
    if not pt.started():
        pt.init()

    index = pt.IndexFactory.of("./backend/polygon_index2")
    lm = pt.BatchRetrieve(index, wmodel="Hiemstra_LM")
    results_df = lm.transform(query)


    # Extract the top 10 results
    top_results = results_df.head(10)

    print(results_df)
    

     # Format the top results into a list of dictionaries
    formatted_results = []
    for index, row in top_results.iterrows():
        result_dict = {
            'docid': str(row['docid']),
            'rank': int(row['rank']),
            'score': float(row['score']),
            'docno': str(row['docno']),
        }
        formatted_results.append(result_dict)

    # Format the results and send them to the front-end
    response = {'query': query, 'results': formatted_results, 'recs': rec_dict}
    
    return response

def recommendations(query):
    global df
    
    df_copy = df.copy()

    # Create a CountVectorizer to convert text data to a matrix of token counts
    vectorizer = CountVectorizer()

    stored_queries_matrix = vectorizer.fit_transform(df_copy['query'])

    given_query_matrix = vectorizer.transform([query])

    # Calculate cosine similarity
    cosine_sim = cosine_similarity(stored_queries_matrix, given_query_matrix)

    # Get the similarity scores for each stored query
    similarity_scores = cosine_sim.flatten()

    df_copy['CosineSimilarity'] = similarity_scores

    sorted_df = df_copy.sort_values(by='CosineSimilarity', ascending=False)

    sorted_df = sorted_df[sorted_df['query'] != query]

    top_results = sorted_df[['query', 'CosineSimilarity']].head(10)
    # Add a new 'Rank' field
    top_results['Rank'] = range(1, len(top_results) + 1)  

    top_results_dict = top_results.to_dict(orient='records')

    print(top_results)

    return top_results_dict

search("Zelda")