from fastapi import FastAPI
from typing import List
import pandas as pd

app = FastAPI()

# Load your data (export from Laravel as JSON)
attractions = pd.read_json('attractions.json')
bookings = pd.read_json('bookings.json')

def recommend_for_user(user_id: int, top_n: int = 4) -> List[int]:
    user_bookings = bookings[bookings['user_id'] == user_id]
    if user_bookings.empty:
        return attractions.sample(top_n)['id'].tolist()
    # Get all tags from user's bookings
    user_tags = user_bookings['tags'].explode().value_counts().index.tolist()
    user_locs = user_bookings['loc'].unique().tolist() if 'loc' in user_bookings else []
    # Score attractions by tag and location overlap
    def score(row):
        tag_score = len(set(row['tags']).intersection(user_tags))
        loc_score = 1 if row['loc'] in user_locs else 0
        return tag_score + loc_score
    attractions['score'] = attractions.apply(score, axis=1)
    recs = attractions[~attractions['id'].isin(user_bookings['attraction_id'])].sort_values('score', ascending=False)
    return recs.head(top_n)['id'].tolist()

@app.get('/recommend')
def recommend(user_id: int, top_n: int = 4):
    rec_ids = recommend_for_user(user_id, top_n)
    return {'recommendations': rec_ids} 