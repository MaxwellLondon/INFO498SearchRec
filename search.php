<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Search</title>
    <!-- Add Bootstrap CSS link -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">

<div class="jumbotron mt-4">
        <h1 class="display-4">Polygon Index Search</h1>
        <!-- Add a paragraph about the application -->
        <p class="lead">
            This is my search project utilizing text from Polygon.com for indexing. I was able to improve my webscraping method and was able to harness more text than the previous assignment. This collection contains 237 documents. Polygon contains news about tech, gaming and pop culture. With the increased scale of this model, I am hoping that queries are more accurate than before. 
        </p>

        <h2>Terms to try</h2>
        <ul class="list-group">
            <li class="list-group-item">Zelda</li>
            <li class="list-group-item">Technology</li>
            <li class="list-group-item">Gaming</li>
            <li class="list-group-item">Random Test</li>
        </ul>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <form action="search.php" method="post">
                <div class="input-group">
                    <input type="text" class="form-control" name="search_string" value="<?php echo $_POST["search_string"]; ?>" />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
    if (isset($_POST["search_string"])) {
        $search_string = $_POST["search_string"];
        $qfile = fopen("query.py", "w");

        fwrite($qfile, "import pyterrier as pt\n");
        fwrite($qfile, "import pandas as pd\n");
        fwrite($qfile, "import numpy as np\n");
        fwrite($qfile, "from sklearn.metrics.pairwise import cosine_similarity\n");
        fwrite($qfile, "from sklearn.feature_extraction.text import CountVectorizer\n");
        
        fwrite($qfile, "searchString = \"$search_string\"\n");

        fwrite($qfile, "df = pd.read_csv(\"./updatedGameQueries.csv\")\n\n");
        
        fwrite($qfile, "def search(query):\n");
        fwrite($qfile, "\tglobal df  # Assuming you want to use the global df variable\n\n");
        
        fwrite($qfile, "\tif query not in df['query'].values:\n");
        fwrite($qfile, "\t\t# Add a new query row using pd.concat() only if it's not a duplicate\n");
        fwrite($qfile, "\t\tnew_row = pd.DataFrame({'query': [query]})\n");
        fwrite($qfile, "\t\tdf = pd.concat([df, new_row], ignore_index=True)\n\n");
        
        fwrite($qfile, "\tprint(len(df))\n");
        fwrite($qfile, "\trec_dict = recommendations(query)\n");
        
        fwrite($qfile, "\tif not pt.started():\n");
        fwrite($qfile, "\t\tpt.init()\n\n");
        
        fwrite($qfile, "\tindex = pt.IndexFactory.of(\"./polygon_index2\")\n");
        fwrite($qfile, "\tlm = pt.BatchRetrieve(index, wmodel=\"Hiemstra_LM\")\n");
        fwrite($qfile, "\tresults_df = lm.transform(query)\n\n");
        
        fwrite($qfile, "\t# Extract the top 10 results\n");
        fwrite($qfile, "\ttop_results = results_df.head(10)\n\n");
        
        fwrite($qfile, "\t# Format the top results into a list of dictionaries\n");
        fwrite($qfile, "\tformatted_results = []\n");
        fwrite($qfile, "\tfor index, row in top_results.iterrows():\n");
        fwrite($qfile, "\t\tresult_dict = {\n");
        fwrite($qfile, "\t\t\t'docid': str(row['docid']),\n");
        fwrite($qfile, "\t\t\t'rank': int(row['rank']),\n");
        fwrite($qfile, "\t\t\t'score': float(row['score']),\n");
        fwrite($qfile, "\t\t\t'docno': str(row['docno']),\n");
        fwrite($qfile, "\t\t}\n");
        fwrite($qfile, "\t\tformatted_results.append(result_dict)\n\n");
        
        fwrite($qfile, "\t# Format the results and send them to the front-end\n");
        fwrite($qfile, "\tresponse = {'query': query, 'results': formatted_results, 'recs': rec_dict}\n");
        fwrite($qfile, "\treturn response\n\n");
        
        fwrite($qfile, "def recommendations(query):\n");
        fwrite($qfile, "\tglobal df\n\n");
        
        fwrite($qfile, "\tdf_copy = df.copy()\n\n");
        
        fwrite($qfile, "\t# Create a CountVectorizer to convert text data to a matrix of token counts\n");
        fwrite($qfile, "\tvectorizer = CountVectorizer()\n\n");
        
        fwrite($qfile, "\tstored_queries_matrix = vectorizer.fit_transform(df_copy['query'])\n\n");
        
        fwrite($qfile, "\tgiven_query_matrix = vectorizer.transform([query])\n\n");
        
        fwrite($qfile, "\t# Calculate cosine similarity\n");
        fwrite($qfile, "\tcosine_sim = cosine_similarity(stored_queries_matrix, given_query_matrix)\n\n");
        
        fwrite($qfile, "\t# Get the similarity scores for each stored query\n");
        fwrite($qfile, "\tsimilarity_scores = cosine_sim.flatten()\n\n");
        
        fwrite($qfile, "\tdf_copy['CosineSimilarity'] = similarity_scores\n\n");
        
        fwrite($qfile, "\tsorted_df = df_copy.sort_values(by='CosineSimilarity', ascending=False)\n\n");
        
        fwrite($qfile, "\tsorted_df = sorted_df[sorted_df['query'] != query]\n\n");
        
        fwrite($qfile, "\ttop_results = sorted_df[['query', 'CosineSimilarity']].head(10)\n");
        fwrite($qfile, "\t# Add a new 'Rank' field\n");
        fwrite($qfile, "\ttop_results['Rank'] = range(1, len(top_results) + 1)\n\n");
        
        fwrite($qfile, "\ttop_results_dict = top_results.to_dict(orient='records')\n\n");
        
        fwrite($qfile, "\treturn top_results_dict\n\n");

        fwrite($qfile, "print(search(searchString))\n");

        fclose($qfile);

        exec("ls | nc -u 127.0.0.1 10034");
        sleep(3);

        $stream = fopen("output", "r");

        $line=fgets($stream);

        $file_path = "query.py";

        while (($line = fgets($stream)) !== false) {
            echo "<div>$line<div/>\n";
        }

       fclose($stream);

       exec("rm query.py");
       exec("rm output");

    }
?>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
